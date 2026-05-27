<?php

namespace App\Filament\Pages;

use App\Actions\Reporting\RefreshAllSummariesAction;
use App\Enums\SalesImportBatchStatus;
use App\Models\Product;
use App\Models\SalesImportBatch;
use App\Models\SalesRecord;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SalesPOS extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedComputerDesktop;

    protected static ?string $navigationLabel = 'POS Terminal';

    protected static string|\UnitEnum|null $navigationGroup = 'Sales';

    protected static ?string $title = 'POS Terminal';

    protected string $view = 'filament.pages.sales-pos';

    // POS Session state
    public string $scanQuery = '';
    public ?array $scannedProduct = null;
    public int $quantity = 1;
    public array $cart = [];
    public ?string $notes = null;

    public static function canAccess(): bool
    {
        return auth()->user()?->can('viewAny', SalesImportBatch::class) ?? false;
    }

    public function mount(): void
    {
        $this->clearCart();
    }

    public function updatedScanQuery(): void
    {
        $query = trim($this->scanQuery);

        if (blank($query)) {
            return;
        }

        // Clean query standardizations (scientific notation, decimals)
        if (preg_match('/^\d+\.0+$/', $query)) {
            $query = explode('.', $query)[0];
        }
        if (stripos($query, 'e') !== false && is_numeric($query)) {
            $query = sprintf('%.0f', (float) $query);
        }

        // Find the product
        $product = Product::query()
            ->with('category:id,name')
            ->where('barcode', $query)
            ->orWhere('sku', $query)
            ->first();

        if (! $product) {
            Notification::make()
                ->title('Product Not Found')
                ->body("No active product matches code: \"{$query}\"")
                ->warning()
                ->send();
            
            $this->scanQuery = '';
            $this->dispatch('focus-scan');
            return;
        }

        // Check if item is already in the cart - if yes, auto-increment for speed!
        if (isset($this->cart[$product->id])) {
            $this->cart[$product->id]['quantity']++;
            $this->cart[$product->id]['total'] = round($this->cart[$product->id]['quantity'] * $this->cart[$product->id]['unit_price'], 2);
            
            Notification::make()
                ->title('Quantity Incremented')
                ->body("{$product->name} quantity is now {$this->cart[$product->id]['quantity']}.")
                ->success()
                ->send();

            $this->scanQuery = '';
            $this->dispatch('focus-scan');
            return;
        }

        // Otherwise, open detail preview card and focus quantity field
        $this->scannedProduct = [
            'id' => $product->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'barcode' => $product->barcode,
            'category' => $product->category?->name ?? 'Uncategorized',
            'selling_price' => (float) $product->selling_price,
            'current_stock' => (int) $product->current_stock,
        ];
        $this->quantity = 1;

        // Shift focus to the quantity input instantly
        $this->dispatch('focus-quantity');
    }

    public function addToCart(): void
    {
        if (! $this->scannedProduct) {
            return;
        }

        if ($this->quantity < 1) {
            Notification::make()
                ->title('Invalid Quantity')
                ->body('Quantity sold must be at least 1.')
                ->danger()
                ->send();
            return;
        }

        $id = $this->scannedProduct['id'];
        $qty = $this->quantity;
        $price = $this->scannedProduct['selling_price'];

        // If inventory is low or negative, issue a friendly warning toast but allow registration
        if ($qty > $this->scannedProduct['current_stock']) {
            Notification::make()
                ->title('Low Inventory Warning')
                ->body("Selling {$qty} units of {$this->scannedProduct['name']} but only {$this->scannedProduct['current_stock']} units are registered in stock.")
                ->warning()
                ->send();
        }

        $this->cart[$id] = [
            'id' => $id,
            'name' => $this->scannedProduct['name'],
            'sku' => $this->scannedProduct['sku'],
            'barcode' => $this->scannedProduct['barcode'],
            'category' => $this->scannedProduct['category'],
            'unit_price' => $price,
            'quantity' => $qty,
            'total' => round($qty * $price, 2),
        ];

        // Reset search states and return focus to scanner input
        $this->scanQuery = '';
        $this->scannedProduct = null;
        $this->quantity = 1;

        Notification::make()
            ->title('Added to Cart')
            ->success()
            ->duration(1500)
            ->send();

        $this->dispatch('focus-scan');
    }

    public function cancelAdd(): void
    {
        $this->scanQuery = '';
        $this->scannedProduct = null;
        $this->quantity = 1;
        $this->dispatch('focus-scan');
    }

    public function removeFromCart(int $productId): void
    {
        unset($this->cart[$productId]);
        
        Notification::make()
            ->title('Item Removed')
            ->info()
            ->send();

        $this->dispatch('focus-scan');
    }

    public function updateQuantity(int $productId, int $newQty): void
    {
        if ($newQty < 1) {
            $this->removeFromCart($productId);
            return;
        }

        if (isset($this->cart[$productId])) {
            $this->cart[$productId]['quantity'] = $newQty;
            $this->cart[$productId]['total'] = round($newQty * $this->cart[$productId]['unit_price'], 2);
        }
    }

    public function clearCart(): void
    {
        $this->cart = [];
        $this->scanQuery = '';
        $this->scannedProduct = null;
        $this->quantity = 1;
        $this->notes = null;
    }

    public function getGrossTotalProperty(): float
    {
        return (float) array_sum(array_column($this->cart, 'total'));
    }

    public function getGrossQuantityProperty(): int
    {
        return (int) array_sum(array_column($this->cart, 'quantity'));
    }

    public function checkout(): void
    {
        if (empty($this->cart)) {
            Notification::make()
                ->title('Cart Empty')
                ->body('Please scan and add items before checking out.')
                ->warning()
                ->send();
            return;
        }

        DB::beginTransaction();

        try {
            $totalQuantity = $this->getGrossQuantityProperty();
            $totalAmount = $this->getGrossTotalProperty();
            
            $batchCode = 'POS-' . strtoupper(Str::random(8));

            $batch = SalesImportBatch::create([
                'batch_code' => $batchCode,
                'uploaded_by' => auth()->id(),
                'status' => SalesImportBatchStatus::PROCESSED,
                'sales_date_from' => now()->toDateString(),
                'sales_date_to' => now()->toDateString(),
                'total_rows' => count($this->cart),
                'successful_rows' => count($this->cart),
                'failed_rows' => 0,
                'total_quantity_sold' => $totalQuantity,
                'total_sales_amount' => $totalAmount,
                'notes' => $this->notes ?? 'Logged directly via POS terminal',
                'processed_at' => now(),
            ]);

            foreach ($this->cart as $item) {
                // Deduct stock in real-time
                $product = Product::findOrFail($item['id']);
                $product->decrement('current_stock', $item['quantity']);

                // Create Sales Record
                SalesRecord::create([
                    'batch_id' => $batch->id,
                    'product_id' => $item['id'],
                    'product_code_snapshot' => $item['sku'],
                    'category_snapshot' => $item['category'],
                    'product_name_snapshot' => $item['name'],
                    'unit_price' => $item['unit_price'],
                    'quantity_sold' => $item['quantity'],
                    'total_amount' => $item['total'],
                    'sales_date' => now()->toDateString(),
                    'sales_time' => now()->format('H:i:s'),
                    'created_by' => auth()->id(),
                ]);
            }

            // Sync metrics and charts
            app(RefreshAllSummariesAction::class)->forDate(now());

            DB::commit();

            Notification::make()
                ->title('Transaction Completed')
                ->body("POS receipt {$batchCode} recorded. Stock deducted successfully!")
                ->success()
                ->persistent()
                ->send();

            $this->clearCart();
            $this->dispatch('focus-scan');

        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            Notification::make()
                ->title('Checkout Failed')
                ->body("An error occurred during transaction logging: {$e->getMessage()}")
                ->danger()
                ->send();
        }
    }
}
