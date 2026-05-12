<?php

namespace App\Filament\Pages;

use App\Actions\Inventory\CreateStockEntryAction;
use App\Models\Category;
use App\Models\Product;
use App\Services\Barcode\BarcodeLookupResult;
use App\Services\Barcode\BarcodeLookupService;
use App\Services\Barcode\BarcodeNormalizer;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * @property-read string $workflowState
 */
class ReceiveStock extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQrCode;

    protected static ?string $navigationLabel = 'Receive Stock';

    protected static string|\UnitEnum|null $navigationGroup = 'Inventory';

    protected static ?int $navigationSort = 0;

    protected static ?string $title = 'Receive Stock';

    protected string $view = 'filament.pages.receive-stock';

    // --- Livewire state ---

    /** The barcode being scanned. */
    public string $barcode = '';

    /** Current workflow state. */
    public string $state = 'scanning';

    /** Lookup result data (serializable). */
    public ?array $lookupData = null;

    /** The found local product ID (null if new product). */
    public ?int $foundProductId = null;

    // --- Stock entry fields ---
    public string $quantityAdded = '';
    public string $unitCostPrice = '';
    public string $unitSellingPrice = '';
    public bool $updateProductPrices = true;
    public string $stockDate = '';
    public string $reference = '';
    public string $note = '';

    // --- New product fields (when product not in DB) ---
    public string $newProductName = '';
    public string $newProductBrand = '';
    public ?int $newProductCategoryId = null;
    public string $newProductPurchasePrice = '';
    public string $newProductSellingPrice = '';
    public string $newProductUnitOfMeasure = 'pcs';

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user?->isSudo() || $user?->isAdmin();
    }

    public function mount(): void
    {
        $this->stockDate = now()->toDateString();
    }

    /**
     * Triggered when the barcode field submits (Enter key or scan).
     */
    public function scanBarcode(): void
    {
        $normalizer = app(BarcodeNormalizer::class);

        try {
            $this->barcode = $normalizer->normalize($this->barcode);
        } catch (ValidationException $e) {
            Notification::make()
                ->title('Invalid barcode')
                ->body(collect($e->errors())->flatten()->first())
                ->danger()
                ->send();

            return;
        }

        $result = app(BarcodeLookupService::class)->lookup($this->barcode);

        $this->lookupData = [
            'barcode' => $result->barcode,
            'source' => $result->source,
            'productName' => $result->productName,
            'brand' => $result->brand,
            'categoryHint' => $result->categoryHint,
            'apiProvider' => $result->apiProvider,
        ];

        if ($result->isLocal()) {
            $this->foundProductId = $result->product?->id;
            $this->unitCostPrice = (string) ($result->product?->purchase_price ?? '');
            $this->unitSellingPrice = (string) ($result->product?->selling_price ?? '');
            $this->state = 'product_found';

            return;
        }

        if ($result->wasFound()) {
            // API or cache result — prefill new product form.
            $this->newProductName = $result->productName ?? '';
            $this->newProductBrand = $result->brand ?? '';
            $this->state = 'api_found';

            return;
        }

        // Not found anywhere — manual entry.
        $this->state = 'manual_entry';
    }

    /**
     * Add stock to an existing product.
     */
    public function addStockToExisting(): void
    {
        $this->validate([
            'quantityAdded' => ['required', 'integer', 'min:1'],
            'unitCostPrice' => ['required', 'numeric', 'min:0'],
            'unitSellingPrice' => ['required', 'numeric', 'min:0'],
            'stockDate' => ['required', 'date'],
        ]);

        try {
            $stockEntry = app(CreateStockEntryAction::class)->execute([
                'product_id' => $this->foundProductId,
                'quantity_added' => (int) $this->quantityAdded,
                'unit_cost_price' => $this->unitCostPrice,
                'unit_selling_price' => $this->unitSellingPrice,
                'stock_date' => $this->stockDate,
                'reference' => $this->reference ?: null,
                'note' => $this->note ?: null,
                'created_by' => auth()->id(),
                'update_product_prices' => $this->updateProductPrices,
            ]);

            Notification::make()
                ->title('Stock added')
                ->body("Added {$stockEntry->quantity_added} units to {$stockEntry->product->name}.")
                ->success()
                ->send();

            $this->resetScanState();
        } catch (ValidationException $e) {
            Notification::make()
                ->title('Validation error')
                ->body(collect($e->errors())->flatten()->first())
                ->danger()
                ->send();
        }
    }

    /**
     * Create a new product and add initial stock.
     */
    public function createProductAndAddStock(): void
    {
        $this->validate([
            'newProductName' => ['required', 'string', 'max:255'],
            'newProductCategoryId' => ['required', 'integer', 'exists:categories,id'],
            'newProductPurchasePrice' => ['required', 'numeric', 'min:0'],
            'newProductSellingPrice' => ['required', 'numeric', 'min:0'],
            'quantityAdded' => ['required', 'integer', 'min:1'],
            'unitCostPrice' => ['required', 'numeric', 'min:0'],
            'unitSellingPrice' => ['required', 'numeric', 'min:0'],
            'stockDate' => ['required', 'date'],
        ]);

        // Validate barcode uniqueness separately since it comes from scan state.
        if (Product::query()->where('barcode', $this->barcode)->exists()) {
            Notification::make()
                ->title('Duplicate barcode')
                ->body('A product with this barcode already exists. Scan it again to add stock instead.')
                ->danger()
                ->send();

            return;
        }

        try {
            DB::transaction(function (): void {
                $source = match ($this->state) {
                    'api_found' => $this->lookupData['apiProvider'] ?? 'api',
                    default => 'manual',
                };

                $product = Product::query()->create([
                    'category_id' => $this->newProductCategoryId,
                    'name' => $this->newProductName,
                    'barcode' => $this->barcode,
                    'brand' => $this->newProductBrand ?: null,
                    'source' => $source,
                    'purchase_price' => $this->newProductPurchasePrice,
                    'selling_price' => $this->newProductSellingPrice,
                    'unit_of_measure' => $this->newProductUnitOfMeasure ?: 'pcs',
                    'reorder_level' => 0,
                ]);

                $stockEntry = app(CreateStockEntryAction::class)->execute([
                    'product_id' => $product->id,
                    'quantity_added' => (int) $this->quantityAdded,
                    'unit_cost_price' => $this->unitCostPrice,
                    'unit_selling_price' => $this->unitSellingPrice,
                    'stock_date' => $this->stockDate,
                    'reference' => $this->reference ?: null,
                    'note' => $this->note ?: null,
                    'created_by' => auth()->id(),
                    'update_product_prices' => true,
                ]);

                Notification::make()
                    ->title('Product created & stock added')
                    ->body("Created {$product->name} with {$stockEntry->quantity_added} units.")
                    ->success()
                    ->send();
            });

            $this->resetScanState();
        } catch (ValidationException $e) {
            Notification::make()
                ->title('Validation error')
                ->body(collect($e->errors())->flatten()->first())
                ->danger()
                ->send();
        }
    }

    /**
     * Reset back to the scanning state for the next barcode.
     */
    public function resetScanState(): void
    {
        $this->barcode = '';
        $this->state = 'scanning';
        $this->lookupData = null;
        $this->foundProductId = null;
        $this->quantityAdded = '';
        $this->unitCostPrice = '';
        $this->unitSellingPrice = '';
        $this->updateProductPrices = true;
        $this->reference = '';
        $this->note = '';
        $this->newProductName = '';
        $this->newProductBrand = '';
        $this->newProductCategoryId = null;
        $this->newProductPurchasePrice = '';
        $this->newProductSellingPrice = '';
        $this->newProductUnitOfMeasure = 'pcs';
    }

    /**
     * Categories for the new-product select.
     *
     * @return array<int, string>
     */
    public function getCategoriesProperty(): array
    {
        return Category::query()
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }
}
