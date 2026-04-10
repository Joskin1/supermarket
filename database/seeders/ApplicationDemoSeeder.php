<?php

namespace Database\Seeders;

use App\Actions\Inventory\CreateStockEntryAction;
use App\Actions\Sales\ApplySalesRecordToInventoryAction;
use App\Enums\RoleEnum;
use App\Enums\SalesImportBatchStatus;
use App\Models\Category;
use App\Models\Product;
use App\Models\SalesImportBatch;
use App\Models\StockEntry;
use App\Models\User;
use App\Support\SalesImport\DailySalesTemplateColumns;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ApplicationDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            SudoUserSeeder::class,
        ]);

        $users = $this->seedUsers();
        $products = $this->seedCatalog();

        $this->seedStockHistory($products, $users);
        $this->seedSalesImports($products, $users);
    }

    /**
     * @return array<string, User>
     */
    protected function seedUsers(): array
    {
        /** @var User $sudo */
        $sudo = User::query()
            ->where('email', env('SUDO_EMAIL', 'akinjoseph221@gmail.com'))
            ->firstOrFail();

        $users = [
            'sudo' => $sudo,
            'store_manager' => $this->upsertAdminUser(
                'Adaeze Manager',
                'store-manager@supermarket.test',
            ),
            'inventory_admin' => $this->upsertAdminUser(
                'Kunle Inventory',
                'inventory-admin@supermarket.test',
            ),
            'sales_admin' => $this->upsertAdminUser(
                'Bola Sales',
                'sales-admin@supermarket.test',
            ),
        ];

        return $users;
    }

    protected function upsertAdminUser(string $name, string $email): User
    {
        /** @var User $user */
        $user = User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
        );

        if (! $user->email_verified_at) {
            $user->forceFill([
                'email_verified_at' => now(),
            ])->save();
        }

        $user->syncRoles([RoleEnum::ADMIN->value]);

        return $user;
    }

    /**
     * @return array<string, Product>
     */
    protected function seedCatalog(): array
    {
        $categories = collect($this->categoryBlueprint())
            ->mapWithKeys(function (array $category): array {
                $record = Category::query()->updateOrCreate(
                    ['slug' => Str::slug($category['name'])],
                    [
                        'name' => $category['name'],
                        'description' => $category['description'],
                        'is_active' => $category['is_active'] ?? true,
                    ],
                );

                return [$category['name'] => $record];
            });

        $products = [];

        foreach ($this->productBlueprint() as $product) {
            /** @var Category $category */
            $category = $categories[$product['category']];

            /** @var Product $record */
            $record = Product::query()->updateOrCreate(
                ['sku' => $product['sku']],
                [
                    'category_id' => $category->id,
                    'product_group' => $product['product_group'],
                    'name' => $product['name'],
                    'slug' => Str::slug(trim($product['name'].' '.$product['variant'])),
                    'brand' => $product['brand'],
                    'variant' => $product['variant'],
                    'description' => $product['description'],
                    'purchase_price' => $product['purchase_price'],
                    'selling_price' => $product['selling_price'],
                    'reorder_level' => $product['reorder_level'],
                    'unit_of_measure' => $product['unit_of_measure'],
                    'is_active' => $product['is_active'] ?? true,
                ],
            );

            $products[$record->sku] = $record->fresh('category');
        }

        return $products;
    }

    /**
     * @param  array<string, Product>  $products
     * @param  array<string, User>  $users
     */
    protected function seedStockHistory(array $products, array $users): void
    {
        foreach ($this->productBlueprint() as $productData) {
            /** @var Product $product */
            $product = $products[$productData['sku']];

            foreach ($productData['stock_entries'] as $index => $entry) {
                if (StockEntry::query()->where('reference', $entry['reference'])->exists()) {
                    continue;
                }

                app(CreateStockEntryAction::class)->execute([
                    'product_id' => $product->id,
                    'quantity_added' => $entry['quantity_added'],
                    'unit_cost_price' => $entry['unit_cost_price'],
                    'unit_selling_price' => $entry['unit_selling_price'],
                    'stock_date' => CarbonImmutable::today()
                        ->subDays($entry['days_ago'])
                        ->toDateString(),
                    'reference' => $entry['reference'],
                    'note' => $entry['note'],
                    'created_by' => $users[$entry['created_by']]->id,
                    'update_product_prices' => $index === array_key_last($productData['stock_entries']),
                ]);
            }
        }
    }

    /**
     * @param  array<string, Product>  $products
     * @param  array<string, User>  $users
     */
    protected function seedSalesImports(array $products, array $users): void
    {
        foreach ($this->salesBatchBlueprint() as $batch) {
            if (SalesImportBatch::query()->where('batch_code', $batch['batch_code'])->exists()) {
                continue;
            }

            $uploadedAt = CarbonImmutable::parse($batch['uploaded_at']);
            $processedAt = isset($batch['processed_at'])
                ? CarbonImmutable::parse($batch['processed_at'])
                : null;
            $rawRows = [];
            $successfulRows = 0;
            $failedRows = 0;
            $quantitySold = 0;
            $salesAmount = 0.0;

            foreach ($batch['sales_rows'] as $row) {
                $rawRows[] = $this->makeSalesCsvRow(
                    $products[$row['sku']],
                    CarbonImmutable::parse($row['date']),
                    $row['quantity_sold'],
                    $row['note'] ?? null,
                );
            }

            foreach ($batch['failure_rows'] as $failure) {
                $rawRows[] = $failure['raw_row'];
            }

            $path = 'sales-imports/demo/'.$uploadedAt->format('Y/m').'/'.$batch['batch_code'].'.csv';
            $content = $this->buildCsv($rawRows);

            Storage::disk('local')->put($path, $content);

            /** @var SalesImportBatch $salesImportBatch */
            $salesImportBatch = SalesImportBatch::query()->create([
                'batch_code' => $batch['batch_code'],
                'file_name' => basename($path),
                'file_path' => $path,
                'original_file_name' => $batch['original_file_name'],
                'file_hash' => hash('sha256', $content),
                'uploaded_by' => $users[$batch['uploaded_by']]->id,
                'status' => $batch['status']->value,
                'notes' => $batch['notes'],
            ]);

            foreach ($batch['sales_rows'] as $row) {
                $product = $products[$row['sku']]->fresh('category');
                $unitPrice = (float) $product->selling_price;
                $quantity = $row['quantity_sold'];

                app(ApplySalesRecordToInventoryAction::class)->execute($salesImportBatch, [
                    'sales_date' => CarbonImmutable::parse($row['date'])->toDateString(),
                    'product' => $product,
                    'product_code' => $product->sku,
                    'category' => $product->category?->name,
                    'product_name' => $product->name,
                    'unit_price' => $unitPrice,
                    'quantity_sold' => $quantity,
                    'total_amount' => round($unitPrice * $quantity, 2),
                    'note' => $row['note'] ?? null,
                ]);

                $successfulRows++;
                $quantitySold += $quantity;
                $salesAmount += $unitPrice * $quantity;
            }

            foreach ($batch['failure_rows'] as $rowIndex => $failure) {
                $salesImportBatch->failures()->create([
                    'row_number' => $successfulRows + $rowIndex + 2,
                    'raw_row' => $failure['raw_row'],
                    'error_messages' => $failure['error_messages'],
                    'product_code' => $failure['raw_row']['product_code'] ?? null,
                    'product_name' => $failure['raw_row']['product_name'] ?? null,
                    'sales_date' => filled($failure['raw_row']['date'] ?? null)
                        ? CarbonImmutable::parse($failure['raw_row']['date'])->toDateString()
                        : null,
                ]);

                $failedRows++;
            }

            $dates = collect($rawRows)
                ->pluck('date')
                ->filter(fn (mixed $date): bool => filled($date))
                ->map(fn (string $date): CarbonImmutable => CarbonImmutable::parse($date))
                ->sort()
                ->values();

            $salesImportBatch->forceFill([
                'status' => $batch['status'],
                'sales_date_from' => $dates->first()?->toDateString(),
                'sales_date_to' => $dates->last()?->toDateString(),
                'total_rows' => $batch['status'] === SalesImportBatchStatus::UPLOADED ? 0 : ($successfulRows + $failedRows),
                'successful_rows' => $batch['status'] === SalesImportBatchStatus::UPLOADED ? 0 : $successfulRows,
                'failed_rows' => $batch['status'] === SalesImportBatchStatus::UPLOADED ? 0 : $failedRows,
                'total_quantity_sold' => $batch['status'] === SalesImportBatchStatus::UPLOADED ? 0 : $quantitySold,
                'total_sales_amount' => $batch['status'] === SalesImportBatchStatus::UPLOADED ? 0 : round($salesAmount, 2),
                'processed_at' => $batch['status'] === SalesImportBatchStatus::UPLOADED ? null : $processedAt,
            ])->save();

            SalesImportBatch::query()
                ->whereKey($salesImportBatch->id)
                ->update([
                    'created_at' => $uploadedAt,
                    'updated_at' => $processedAt ?? $uploadedAt,
                ]);
        }
    }

    protected function makeSalesCsvRow(
        Product $product,
        CarbonImmutable $salesDate,
        int $quantitySold,
        ?string $note = null,
    ): array {
        $unitPrice = (float) $product->selling_price;

        return [
            'date' => $salesDate->toDateString(),
            'product_code' => $product->sku,
            'category' => $product->category?->name,
            'product_name' => $product->name,
            'unit_price' => number_format($unitPrice, 2, '.', ''),
            'quantity_sold' => $quantitySold,
            'total_amount' => number_format($unitPrice * $quantitySold, 2, '.', ''),
            'note' => $note ?? '',
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    protected function buildCsv(array $rows): string
    {
        $headings = DailySalesTemplateColumns::all();

        $lines = [
            implode(',', $headings),
        ];

        foreach ($rows as $row) {
            $orderedRow = array_map(
                fn (string $heading): string => $this->escapeCsvValue($row[$heading] ?? ''),
                $headings,
            );

            $lines[] = implode(',', $orderedRow);
        }

        return implode("\n", $lines)."\n";
    }

    protected function escapeCsvValue(mixed $value): string
    {
        $string = (string) $value;

        if (! str_contains($string, ',') && ! str_contains($string, '"') && ! str_contains($string, "\n")) {
            return $string;
        }

        return '"'.str_replace('"', '""', $string).'"';
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function categoryBlueprint(): array
    {
        return [
            [
                'name' => 'Beverages',
                'description' => 'Soft drinks, bottled water, juices, and quick-serve refreshment items.',
            ],
            [
                'name' => 'Groceries',
                'description' => 'Pantry staples such as grains, sugar, oil, and meal-prep essentials.',
            ],
            [
                'name' => 'Toiletries',
                'description' => 'Daily personal care items that move steadily every week.',
            ],
            [
                'name' => 'Household Items',
                'description' => 'Cleaning and upkeep products for home restocking trips.',
            ],
            [
                'name' => 'Snacks & Confectionery',
                'description' => 'Impulse snacks and shelf-stable treats near the counter and aisles.',
            ],
            [
                'name' => 'Dairy & Breakfast',
                'description' => 'Breakfast staples, cereals, and powdered milk products.',
            ],
            [
                'name' => 'Baby Care',
                'description' => 'Trusted baby essentials for repeat-family purchases.',
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function productBlueprint(): array
    {
        return [
            [
                'category' => 'Beverages',
                'product_group' => 'Soft Drink',
                'name' => 'Coca-Cola Classic Soft Drink',
                'sku' => 'BEV-COKE-50CL',
                'brand' => 'Coca-Cola',
                'variant' => '50cl',
                'description' => 'Fast-moving chilled soft drink for daily walk-in traffic.',
                'purchase_price' => 520,
                'selling_price' => 650,
                'reorder_level' => 18,
                'unit_of_measure' => 'bottle',
                'stock_entries' => [
                    $this->stockEntry('DEMO-STOCK-BEV-COKE-50CL-01', 14, 36, 500, 620, 'sales_admin', 'Opening cooler stock before the week began.'),
                    $this->stockEntry('DEMO-STOCK-BEV-COKE-50CL-02', 5, 24, 520, 650, 'inventory_admin', 'Weekend replenishment for beverage demand.'),
                ],
            ],
            [
                'category' => 'Beverages',
                'product_group' => 'Soft Drink',
                'name' => 'Fanta Orange Soft Drink',
                'sku' => 'BEV-FANTA-50CL',
                'brand' => 'Fanta',
                'variant' => '50cl',
                'description' => 'Popular orange soda sold from the drinks aisle and chiller.',
                'purchase_price' => 520,
                'selling_price' => 650,
                'reorder_level' => 18,
                'unit_of_measure' => 'bottle',
                'stock_entries' => [
                    $this->stockEntry('DEMO-STOCK-BEV-FANTA-50CL-01', 13, 30, 500, 620, 'sales_admin', 'Initial shelf fill for the orange soda bay.'),
                    $this->stockEntry('DEMO-STOCK-BEV-FANTA-50CL-02', 5, 18, 520, 650, 'inventory_admin', 'Replenished after strong weekend movement.'),
                ],
            ],
            [
                'category' => 'Beverages',
                'product_group' => 'Soft Drink',
                'name' => 'Pepsi Cola Soft Drink',
                'sku' => 'BEV-PEPSI-50CL',
                'brand' => 'Pepsi',
                'variant' => '50cl',
                'description' => 'Alternative cola option with steady cooler sales.',
                'purchase_price' => 520,
                'selling_price' => 650,
                'reorder_level' => 18,
                'unit_of_measure' => 'bottle',
                'stock_entries' => [
                    $this->stockEntry('DEMO-STOCK-BEV-PEPSI-50CL-01', 12, 24, 500, 620, 'sales_admin', 'Base stock for the cola shelf.'),
                    $this->stockEntry('DEMO-STOCK-BEV-PEPSI-50CL-02', 5, 12, 520, 650, 'inventory_admin', 'Short top-up to balance cola options.'),
                ],
            ],
            [
                'category' => 'Beverages',
                'product_group' => 'Juice',
                'name' => 'Five Alive Pulpy Orange Juice',
                'sku' => 'BEV-FIVEALIVE-1L',
                'brand' => 'Five Alive',
                'variant' => '1L',
                'description' => 'Family-sized juice line often bought with breakfast items.',
                'purchase_price' => 1180,
                'selling_price' => 1500,
                'reorder_level' => 8,
                'unit_of_measure' => 'carton',
                'stock_entries' => [
                    $this->stockEntry('DEMO-STOCK-BEV-FIVEALIVE-1L-01', 12, 12, 1150, 1450, 'store_manager', 'Initial juice shelf arrangement.'),
                    $this->stockEntry('DEMO-STOCK-BEV-FIVEALIVE-1L-02', 6, 8, 1180, 1500, 'inventory_admin', 'Refill before the new sales week.'),
                ],
            ],
            [
                'category' => 'Beverages',
                'product_group' => 'Water',
                'name' => 'Nestle Pure Life Water',
                'sku' => 'BEV-PURELIFE-75CL',
                'brand' => 'Nestle',
                'variant' => '75cl',
                'description' => 'Bottled water line with fast daily turnover.',
                'purchase_price' => 250,
                'selling_price' => 350,
                'reorder_level' => 6,
                'unit_of_measure' => 'bottle',
                'stock_entries' => [
                    $this->stockEntry('DEMO-STOCK-BEV-PURELIFE-75CL-01', 11, 12, 240, 330, 'sales_admin', 'Loaded into the water section for weekday trade.'),
                    $this->stockEntry('DEMO-STOCK-BEV-PURELIFE-75CL-02', 4, 6, 250, 350, 'inventory_admin', 'Small refill before the sales upload period.'),
                ],
            ],
            [
                'category' => 'Groceries',
                'product_group' => 'Rice',
                'name' => 'Mama Gold Parboiled Rice',
                'sku' => 'GRO-RICE-MG-5KG',
                'brand' => 'Mama Gold',
                'variant' => '5kg',
                'description' => 'Reliable rice staple for weekly family shopping.',
                'purchase_price' => 7700,
                'selling_price' => 8900,
                'reorder_level' => 5,
                'unit_of_measure' => 'bag',
                'stock_entries' => [
                    $this->stockEntry('DEMO-STOCK-GRO-RICE-MG-5KG-01', 10, 8, 7600, 8800, 'store_manager', 'Base rice stock from the last supply run.'),
                    $this->stockEntry('DEMO-STOCK-GRO-RICE-MG-5KG-02', 6, 6, 7700, 8900, 'inventory_admin', 'Supplementary rice stock for the dry-goods lane.'),
                ],
            ],
            [
                'category' => 'Groceries',
                'product_group' => 'Pasta',
                'name' => 'Golden Penny Spaghetti',
                'sku' => 'GRO-SPAG-GP-500G',
                'brand' => 'Golden Penny',
                'variant' => '500g',
                'description' => 'Fast pantry essential often purchased in multiples.',
                'purchase_price' => 650,
                'selling_price' => 900,
                'reorder_level' => 10,
                'unit_of_measure' => 'pack',
                'stock_entries' => [
                    $this->stockEntry('DEMO-STOCK-GRO-SPAG-GP-500G-01', 10, 18, 620, 860, 'store_manager', 'Pasta shelf opening balance.'),
                    $this->stockEntry('DEMO-STOCK-GRO-SPAG-GP-500G-02', 5, 12, 650, 900, 'inventory_admin', 'Quick restock before the weekend rush.'),
                ],
            ],
            [
                'category' => 'Groceries',
                'product_group' => 'Cooking Oil',
                'name' => "Devon King's Vegetable Oil",
                'sku' => 'GRO-OIL-DK-1L',
                'brand' => "Devon King's",
                'variant' => '1L',
                'description' => 'Mid-sized cooking oil line for everyday household restocks.',
                'purchase_price' => 2050,
                'selling_price' => 2400,
                'reorder_level' => 6,
                'unit_of_measure' => 'bottle',
                'stock_entries' => [
                    $this->stockEntry('DEMO-STOCK-GRO-OIL-DK-1L-01', 9, 10, 2000, 2350, 'store_manager', 'Cooking oil base stock from regular supplier.'),
                    $this->stockEntry('DEMO-STOCK-GRO-OIL-DK-1L-02', 4, 6, 2050, 2400, 'inventory_admin', 'Top-up after midweek family shopping.'),
                ],
            ],
            [
                'category' => 'Groceries',
                'product_group' => 'Sugar',
                'name' => 'Dangote Sugar',
                'sku' => 'GRO-SUGAR-DAN-1KG',
                'brand' => 'Dangote',
                'variant' => '1kg',
                'description' => 'Household sugar staple with steady repeat demand.',
                'purchase_price' => 1450,
                'selling_price' => 1800,
                'reorder_level' => 6,
                'unit_of_measure' => 'bag',
                'stock_entries' => [
                    $this->stockEntry('DEMO-STOCK-GRO-SUGAR-DAN-1KG-01', 9, 12, 1400, 1750, 'store_manager', 'Sugar restock for the dry pantry section.'),
                    $this->stockEntry('DEMO-STOCK-GRO-SUGAR-DAN-1KG-02', 4, 8, 1450, 1800, 'inventory_admin', 'Refill after early-week sales.'),
                ],
            ],
            [
                'category' => 'Groceries',
                'product_group' => 'Swallow',
                'name' => 'Semovita',
                'sku' => 'GRO-SEMOVITA-1KG',
                'brand' => 'Honeywell',
                'variant' => '1kg',
                'description' => 'Semolina staple that moves strongly at month-end and weekends.',
                'purchase_price' => 1320,
                'selling_price' => 1700,
                'reorder_level' => 5,
                'unit_of_measure' => 'pack',
                'stock_entries' => [
                    $this->stockEntry('DEMO-STOCK-GRO-SEMOVITA-1KG-01', 9, 8, 1280, 1650, 'store_manager', 'Dry-goods opening stock for semolina items.'),
                    $this->stockEntry('DEMO-STOCK-GRO-SEMOVITA-1KG-02', 4, 6, 1320, 1700, 'inventory_admin', 'Late refill before daily sales reconciliation.'),
                ],
            ],
            [
                'category' => 'Toiletries',
                'product_group' => 'Toothpaste',
                'name' => 'Colgate MaxFresh Toothpaste',
                'sku' => 'TOI-TP-COL-120G',
                'brand' => 'Colgate',
                'variant' => '120g',
                'description' => 'Everyday toothpaste with dependable basket frequency.',
                'purchase_price' => 1300,
                'selling_price' => 1650,
                'reorder_level' => 8,
                'unit_of_measure' => 'tube',
                'stock_entries' => [
                    $this->stockEntry('DEMO-STOCK-TOI-TP-COL-120G-01', 11, 16, 1250, 1600, 'sales_admin', 'Shelf reset for oral care products.'),
                    $this->stockEntry('DEMO-STOCK-TOI-TP-COL-120G-02', 5, 8, 1300, 1650, 'inventory_admin', 'Oral care replenishment after steady movement.'),
                ],
            ],
            [
                'category' => 'Toiletries',
                'product_group' => 'Roll-On',
                'name' => 'Nivea Men Roll-On',
                'sku' => 'TOI-ROLL-NIV-50ML',
                'brand' => 'Nivea',
                'variant' => '50ml',
                'description' => 'Fast recognizable deodorant line for quick top-up purchases.',
                'purchase_price' => 2600,
                'selling_price' => 3200,
                'reorder_level' => 8,
                'unit_of_measure' => 'bottle',
                'stock_entries' => [
                    $this->stockEntry('DEMO-STOCK-TOI-ROLL-NIV-50ML-01', 10, 8, 2550, 3150, 'sales_admin', 'Base stock for the male grooming shelf.'),
                    $this->stockEntry('DEMO-STOCK-TOI-ROLL-NIV-50ML-02', 4, 4, 2600, 3200, 'inventory_admin', 'Short replenishment to maintain availability.'),
                ],
            ],
            [
                'category' => 'Toiletries',
                'product_group' => 'Bathing Soap',
                'name' => 'Dettol Original Soap',
                'sku' => 'TOI-SOAP-DETTOL-175G',
                'brand' => 'Dettol',
                'variant' => '175g',
                'description' => 'Trusted antiseptic bathing soap with constant repeat sales.',
                'purchase_price' => 650,
                'selling_price' => 850,
                'reorder_level' => 10,
                'unit_of_measure' => 'bar',
                'stock_entries' => [
                    $this->stockEntry('DEMO-STOCK-TOI-SOAP-DETTOL-175G-01', 12, 20, 620, 820, 'sales_admin', 'Bulk soap restock for toiletries aisle.'),
                    $this->stockEntry('DEMO-STOCK-TOI-SOAP-DETTOL-175G-02', 5, 10, 650, 850, 'inventory_admin', 'Secondary shelf fill ahead of weekend traffic.'),
                ],
            ],
            [
                'category' => 'Toiletries',
                'product_group' => 'Toothbrush',
                'name' => 'Oral-B Medium Toothbrush',
                'sku' => 'TOI-TB-ORALB-MED',
                'brand' => 'Oral-B',
                'variant' => 'Medium',
                'description' => 'Single toothbrush line used to test low-stock behavior.',
                'purchase_price' => 480,
                'selling_price' => 750,
                'reorder_level' => 6,
                'unit_of_measure' => 'pcs',
                'stock_entries' => [
                    $this->stockEntry('DEMO-STOCK-TOI-TB-ORALB-MED-01', 11, 6, 460, 700, 'sales_admin', 'Oral care accessory shelf launch stock.'),
                    $this->stockEntry('DEMO-STOCK-TOI-TB-ORALB-MED-02', 5, 4, 480, 750, 'inventory_admin', 'Minor refill before sales upload review.'),
                ],
            ],
            [
                'category' => 'Household Items',
                'product_group' => 'Detergent',
                'name' => 'Ariel Ultra Clean Detergent',
                'sku' => 'HOU-DET-ARIEL-850G',
                'brand' => 'Ariel',
                'variant' => '850g',
                'description' => 'Laundry detergent line with higher-ticket household margins.',
                'purchase_price' => 3400,
                'selling_price' => 4200,
                'reorder_level' => 7,
                'unit_of_measure' => 'pack',
                'stock_entries' => [
                    $this->stockEntry('DEMO-STOCK-HOU-DET-ARIEL-850G-01', 10, 10, 3300, 4100, 'store_manager', 'Household aisle detergent reset.'),
                    $this->stockEntry('DEMO-STOCK-HOU-DET-ARIEL-850G-02', 4, 6, 3400, 4200, 'inventory_admin', 'Quick household top-up after midweek restocking.'),
                ],
            ],
            [
                'category' => 'Household Items',
                'product_group' => 'Bleach',
                'name' => 'Hypo Original Bleach',
                'sku' => 'HOU-BLEACH-HYPO-1L',
                'brand' => 'Hypo',
                'variant' => '1L',
                'description' => 'Fast-moving bleach used to demonstrate out-of-stock scenarios.',
                'purchase_price' => 950,
                'selling_price' => 1300,
                'reorder_level' => 6,
                'unit_of_measure' => 'bottle',
                'stock_entries' => [
                    $this->stockEntry('DEMO-STOCK-HOU-BLEACH-HYPO-1L-01', 10, 8, 920, 1250, 'store_manager', 'Cleaning aisle bleach allocation.'),
                    $this->stockEntry('DEMO-STOCK-HOU-BLEACH-HYPO-1L-02', 4, 4, 950, 1300, 'inventory_admin', 'Top-up stock before the final daily imports.'),
                ],
            ],
            [
                'category' => 'Household Items',
                'product_group' => 'Toilet Cleaner',
                'name' => 'Harpic Power Plus Toilet Cleaner',
                'sku' => 'HOU-HARPIC-500ML',
                'brand' => 'Harpic',
                'variant' => '500ml',
                'description' => 'Bathroom cleaner frequently bought with bleach and detergents.',
                'purchase_price' => 1450,
                'selling_price' => 1850,
                'reorder_level' => 4,
                'unit_of_measure' => 'bottle',
                'stock_entries' => [
                    $this->stockEntry('DEMO-STOCK-HOU-HARPIC-500ML-01', 9, 6, 1400, 1800, 'store_manager', 'Bathroom cleaner shelf opening stock.'),
                    $this->stockEntry('DEMO-STOCK-HOU-HARPIC-500ML-02', 4, 4, 1450, 1850, 'inventory_admin', 'Replenished with the weekly household order.'),
                ],
            ],
            [
                'category' => 'Snacks & Confectionery',
                'product_group' => 'Pastry Snack',
                'name' => 'Gala Sausage Roll',
                'sku' => 'SNK-GALA-80G',
                'brand' => 'Gala',
                'variant' => '80g',
                'description' => 'Impulse counter snack with high turnover and quick restocking needs.',
                'purchase_price' => 250,
                'selling_price' => 400,
                'reorder_level' => 12,
                'unit_of_measure' => 'pcs',
                'stock_entries' => [
                    $this->stockEntry('DEMO-STOCK-SNK-GALA-80G-01', 10, 16, 240, 380, 'sales_admin', 'Counter snack tray opening stock.'),
                    $this->stockEntry('DEMO-STOCK-SNK-GALA-80G-02', 4, 8, 250, 400, 'inventory_admin', 'Refilled after lunch-hour snack demand.'),
                ],
            ],
            [
                'category' => 'Snacks & Confectionery',
                'product_group' => 'Crisps',
                'name' => 'Pringles Original',
                'sku' => 'SNK-PRINGLES-165G',
                'brand' => 'Pringles',
                'variant' => '165g',
                'description' => 'Premium snack tube with slower but higher-value movement.',
                'purchase_price' => 2900,
                'selling_price' => 3500,
                'reorder_level' => 4,
                'unit_of_measure' => 'can',
                'stock_entries' => [
                    $this->stockEntry('DEMO-STOCK-SNK-PRINGLES-165G-01', 9, 8, 2850, 3400, 'store_manager', 'Snack aisle feature stock.'),
                    $this->stockEntry('DEMO-STOCK-SNK-PRINGLES-165G-02', 4, 4, 2900, 3500, 'inventory_admin', 'Premium snack top-up before weekend demand.'),
                ],
            ],
            [
                'category' => 'Snacks & Confectionery',
                'product_group' => 'Confectionery',
                'name' => 'Cadbury TomTom Rolls',
                'sku' => 'SNK-TOMTOM-40S',
                'brand' => 'Cadbury',
                'variant' => '40s',
                'description' => 'Counter candy line with steady add-on purchases.',
                'purchase_price' => 520,
                'selling_price' => 800,
                'reorder_level' => 5,
                'unit_of_measure' => 'roll',
                'stock_entries' => [
                    $this->stockEntry('DEMO-STOCK-SNK-TOMTOM-40S-01', 9, 12, 500, 760, 'sales_admin', 'Counter confectionery opening count.'),
                    $this->stockEntry('DEMO-STOCK-SNK-TOMTOM-40S-02', 4, 8, 520, 800, 'inventory_admin', 'Refilled for checkout impulse purchases.'),
                ],
            ],
            [
                'category' => 'Dairy & Breakfast',
                'product_group' => 'Milk',
                'name' => 'Peak Full Cream Milk Powder',
                'sku' => 'DBR-PEAK-400G',
                'brand' => 'Peak',
                'variant' => '400g',
                'description' => 'High-demand powdered milk for breakfast shoppers.',
                'purchase_price' => 3200,
                'selling_price' => 3800,
                'reorder_level' => 6,
                'unit_of_measure' => 'tin',
                'stock_entries' => [
                    $this->stockEntry('DEMO-STOCK-DBR-PEAK-400G-01', 10, 6, 3150, 3700, 'store_manager', 'Breakfast shelf anchor stock.'),
                    $this->stockEntry('DEMO-STOCK-DBR-PEAK-400G-02', 4, 4, 3200, 3800, 'inventory_admin', 'Small milk refill before the current day trade.'),
                ],
            ],
            [
                'category' => 'Dairy & Breakfast',
                'product_group' => 'Chocolate Drink',
                'name' => 'Milo Refill',
                'sku' => 'DBR-MILO-400G',
                'brand' => 'Milo',
                'variant' => '400g',
                'description' => 'Breakfast chocolate drink refill with stable repeat sales.',
                'purchase_price' => 2650,
                'selling_price' => 3200,
                'reorder_level' => 6,
                'unit_of_measure' => 'pack',
                'stock_entries' => [
                    $this->stockEntry('DEMO-STOCK-DBR-MILO-400G-01', 10, 8, 2600, 3100, 'store_manager', 'Breakfast drinks opening allocation.'),
                    $this->stockEntry('DEMO-STOCK-DBR-MILO-400G-02', 4, 6, 2650, 3200, 'inventory_admin', 'Replenished alongside milk products.'),
                ],
            ],
            [
                'category' => 'Dairy & Breakfast',
                'product_group' => 'Cereal',
                'name' => "Kellogg's Corn Flakes",
                'sku' => 'DBR-CORNFLAKES-500G',
                'brand' => "Kellogg's",
                'variant' => '500g',
                'description' => 'Breakfast cereal line seeded to sit right on the reorder threshold.',
                'purchase_price' => 3550,
                'selling_price' => 4200,
                'reorder_level' => 5,
                'unit_of_measure' => 'box',
                'stock_entries' => [
                    $this->stockEntry('DEMO-STOCK-DBR-CORNFLAKES-500G-01', 9, 5, 3500, 4100, 'store_manager', 'Cereal shelf opening balance.'),
                    $this->stockEntry('DEMO-STOCK-DBR-CORNFLAKES-500G-02', 4, 4, 3550, 4200, 'inventory_admin', 'Small top-up to keep the cereal section full.'),
                ],
            ],
            [
                'category' => 'Baby Care',
                'product_group' => 'Lotion',
                'name' => 'Cussons Baby Lotion',
                'sku' => 'BABY-CUSSONS-200ML',
                'brand' => 'Cussons',
                'variant' => '200ml',
                'description' => 'Trusted baby lotion purchased by returning family customers.',
                'purchase_price' => 2250,
                'selling_price' => 2800,
                'reorder_level' => 3,
                'unit_of_measure' => 'bottle',
                'stock_entries' => [
                    $this->stockEntry('DEMO-STOCK-BABY-CUSSONS-200ML-01', 9, 5, 2200, 2700, 'store_manager', 'Baby care shelf opening setup.'),
                    $this->stockEntry('DEMO-STOCK-BABY-CUSSONS-200ML-02', 4, 3, 2250, 2800, 'inventory_admin', 'Minor refill after repeat family visits.'),
                ],
            ],
            [
                'category' => 'Baby Care',
                'product_group' => 'Diaper',
                'name' => 'Molfix Baby Diapers Midi',
                'sku' => 'BABY-MOLFIX-MIDI-28S',
                'brand' => 'Molfix',
                'variant' => '28s',
                'description' => 'Core diaper pack used to show higher-ticket baby care sales.',
                'purchase_price' => 5200,
                'selling_price' => 6200,
                'reorder_level' => 4,
                'unit_of_measure' => 'pack',
                'stock_entries' => [
                    $this->stockEntry('DEMO-STOCK-BABY-MOLFIX-MIDI-28S-01', 9, 6, 5100, 6100, 'store_manager', 'Diaper shelf opening stock.'),
                    $this->stockEntry('DEMO-STOCK-BABY-MOLFIX-MIDI-28S-02', 4, 4, 5200, 6200, 'inventory_admin', 'Small replenishment before current day trading.'),
                ],
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function salesBatchBlueprint(): array
    {
        $threeDaysAgo = CarbonImmutable::today()->subDays(3);
        $yesterday = CarbonImmutable::yesterday();
        $today = CarbonImmutable::today();

        return [
            [
                'batch_code' => 'SIB-DEMO-'.$threeDaysAgo->format('Ymd').'-A',
                'original_file_name' => 'daily-sales-'.$threeDaysAgo->format('Y-m-d').'-morning.csv',
                'uploaded_by' => 'sales_admin',
                'status' => SalesImportBatchStatus::PROCESSED,
                'uploaded_at' => $threeDaysAgo->setTime(18, 10, 0)->toDateTimeString(),
                'processed_at' => $threeDaysAgo->setTime(18, 18, 0)->toDateTimeString(),
                'notes' => 'Morning and afternoon paper sales were reconciled without issues.',
                'sales_rows' => [
                    $this->salesRow($threeDaysAgo, 'BEV-COKE-50CL', 8, 'Packed from the front chiller display.'),
                    $this->salesRow($threeDaysAgo, 'BEV-FANTA-50CL', 4, 'Included in lunch-time cooler sales.'),
                    $this->salesRow($threeDaysAgo, 'BEV-FIVEALIVE-1L', 4, 'Breakfast basket sales from family shoppers.'),
                    $this->salesRow($threeDaysAgo, 'GRO-RICE-MG-5KG', 2, 'Two family restock purchases.'),
                    $this->salesRow($threeDaysAgo, 'TOI-TP-COL-120G', 3, 'Toiletries shelf movement from repeat buyers.'),
                    $this->salesRow($threeDaysAgo, 'TOI-SOAP-DETTOL-175G', 6, 'Soap sold strongly during evening rush.'),
                    $this->salesRow($threeDaysAgo, 'HOU-DET-ARIEL-850G', 1, 'Single detergent basket add-on.'),
                    $this->salesRow($threeDaysAgo, 'SNK-GALA-80G', 6, 'Counter snack movement during school pickup hours.'),
                    $this->salesRow($threeDaysAgo, 'DBR-PEAK-400G', 2, 'Breakfast essentials sale at checkout.'),
                ],
                'failure_rows' => [],
            ],
            [
                'batch_code' => 'SIB-DEMO-'.$yesterday->format('Ymd').'-B',
                'original_file_name' => 'daily-sales-'.$yesterday->format('Y-m-d').'-closing.csv',
                'uploaded_by' => 'store_manager',
                'status' => SalesImportBatchStatus::PROCESSED_WITH_FAILURES,
                'uploaded_at' => $yesterday->setTime(20, 6, 0)->toDateTimeString(),
                'processed_at' => $yesterday->setTime(20, 14, 0)->toDateTimeString(),
                'notes' => 'Closing sheet uploaded with two cashier entry issues flagged for review.',
                'sales_rows' => [
                    $this->salesRow($yesterday, 'BEV-COKE-50CL', 10, 'Evening cooler sales after office closing traffic.'),
                    $this->salesRow($yesterday, 'BEV-PEPSI-50CL', 6, 'Alternative cola sales from walk-in customers.'),
                    $this->salesRow($yesterday, 'BEV-PURELIFE-75CL', 8, 'Water sold steadily throughout the day.'),
                    $this->salesRow($yesterday, 'GRO-SUGAR-DAN-1KG', 4, 'Sugar restocks for home shoppers.'),
                    $this->salesRow($yesterday, 'TOI-TP-COL-120G', 4, 'Additional toothpaste sales captured from the back shelf.'),
                    $this->salesRow($yesterday, 'TOI-ROLL-NIV-50ML', 2, 'Personal care purchases recorded at closing.'),
                    $this->salesRow($yesterday, 'HOU-BLEACH-HYPO-1L', 5, 'Cleaning products moved strongly before weekend prep.'),
                ],
                'failure_rows' => [
                    $this->failureRow(
                        $yesterday,
                        'BEV-SPRITE-50CL',
                        'Beverages',
                        'Sprite Lemon-Lime Soft Drink',
                        650,
                        3,
                        1950,
                        'Product code was written from memory on the offline sheet.',
                        ['The product code does not match any existing product.'],
                    ),
                    $this->failureRow(
                        $yesterday,
                        'TOI-TB-ORALB-MED',
                        'Toiletries',
                        'Oral-B Medium Toothbrush',
                        750,
                        14,
                        10500,
                        'Cashier count exceeded available stock.',
                        ['The quantity sold exceeds the current stock for this product.'],
                    ),
                ],
            ],
            [
                'batch_code' => 'SIB-DEMO-'.$today->format('Ymd').'-C',
                'original_file_name' => 'daily-sales-'.$today->format('Y-m-d').'-morning.csv',
                'uploaded_by' => 'sales_admin',
                'status' => SalesImportBatchStatus::PROCESSED,
                'uploaded_at' => $today->setTime(13, 2, 0)->toDateTimeString(),
                'processed_at' => $today->setTime(13, 12, 0)->toDateTimeString(),
                'notes' => 'Morning shift uploaded cleanly after product-by-product cross-check.',
                'sales_rows' => [
                    $this->salesRow($today, 'BEV-COKE-50CL', 16, 'Strong cooler movement before lunch.'),
                    $this->salesRow($today, 'BEV-FANTA-50CL', 16, 'Orange soda moved with school-run traffic.'),
                    $this->salesRow($today, 'BEV-PURELIFE-75CL', 10, 'Water stock sold out during hot afternoon demand.'),
                    $this->salesRow($today, 'GRO-SEMOVITA-1KG', 9, 'Weekend meal prep shopping.'),
                    $this->salesRow($today, 'TOI-SOAP-DETTOL-175G', 10, 'Soap sales recorded from the front toiletries rack.'),
                    $this->salesRow($today, 'TOI-TB-ORALB-MED', 8, 'Remaining toothbrush stock sold down quickly.'),
                    $this->salesRow($today, 'SNK-GALA-80G', 14, 'Counter snacks sold heavily during lunch period.'),
                    $this->salesRow($today, 'DBR-PEAK-400G', 7, 'Milk tins moved with breakfast restocks.'),
                    $this->salesRow($today, 'DBR-MILO-400G', 6, 'Milo refill purchases from repeat shoppers.'),
                    $this->salesRow($today, 'DBR-CORNFLAKES-500G', 4, 'Cereal sold in family breakfast baskets.'),
                    $this->salesRow($today, 'BABY-MOLFIX-MIDI-28S', 3, 'Diaper packs sold to regular family customers.'),
                    $this->salesRow($today, 'SNK-PRINGLES-165G', 5, 'Premium snack tins sold during evening top-up shopping.'),
                ],
                'failure_rows' => [],
            ],
            [
                'batch_code' => 'SIB-DEMO-'.$today->format('Ymd').'-D',
                'original_file_name' => 'daily-sales-'.$today->format('Y-m-d').'-afternoon.csv',
                'uploaded_by' => 'inventory_admin',
                'status' => SalesImportBatchStatus::PROCESSED_WITH_FAILURES,
                'uploaded_at' => $today->setTime(17, 45, 0)->toDateTimeString(),
                'processed_at' => $today->setTime(17, 55, 0)->toDateTimeString(),
                'notes' => 'Afternoon upload succeeded, but two rows were held back for correction.',
                'sales_rows' => [
                    $this->salesRow($today, 'GRO-SPAG-GP-500G', 12, 'Pasta sales from family dinner restocks.'),
                    $this->salesRow($today, 'GRO-OIL-DK-1L', 5, 'Cooking oil sold in weekday top-up baskets.'),
                    $this->salesRow($today, 'BEV-PEPSI-50CL', 12, 'Pepsi line sold down near closing time.'),
                    $this->salesRow($today, 'TOI-ROLL-NIV-50ML', 3, 'Personal care purchases captured after shift change.'),
                    $this->salesRow($today, 'HOU-HARPIC-500ML', 4, 'Bathroom cleaner sales from household restock baskets.'),
                    $this->salesRow($today, 'HOU-BLEACH-HYPO-1L', 7, 'Bleach stock cleared out before end of day.'),
                    $this->salesRow($today, 'SNK-TOMTOM-40S', 6, 'Checkout confectionery sales were strong.'),
                    $this->salesRow($today, 'BABY-CUSSONS-200ML', 2, 'Baby lotion sold to repeat customers.'),
                ],
                'failure_rows' => [
                    $this->failureRow(
                        $today,
                        'BEV-PEPSI-50CL',
                        'Beverages',
                        'Pepsi Cola Soft Drink',
                        650,
                        2,
                        1000,
                        'Total was written incorrectly on the paper sheet.',
                        ['The total amount must match unit price multiplied by quantity sold.'],
                    ),
                    $this->failureRow(
                        $today,
                        'SNK-LOCALCHIPS-100G',
                        'Snacks & Confectionery',
                        'Local Plantain Chips',
                        700,
                        5,
                        3500,
                        'Product has not been added to the master catalog yet.',
                        ['The product code does not match any existing product.'],
                    ),
                ],
            ],
            [
                'batch_code' => 'SIB-DEMO-'.$today->format('Ymd').'-E',
                'original_file_name' => 'daily-sales-'.$today->format('Y-m-d').'-correction-attempt.csv',
                'uploaded_by' => 'store_manager',
                'status' => SalesImportBatchStatus::FAILED,
                'uploaded_at' => $today->setTime(19, 10, 0)->toDateTimeString(),
                'processed_at' => $today->setTime(19, 16, 0)->toDateTimeString(),
                'notes' => 'Correction upload failed completely and should be replaced with a cleaned file.',
                'sales_rows' => [],
                'failure_rows' => [
                    $this->failureRow(
                        $today,
                        'GRO-RICE-MG-5KG',
                        'Groceries',
                        'Mama Gold Parboiled Rice',
                        8900,
                        40,
                        356000,
                        'Attempted to upload all pending rice sales in one row.',
                        ['The quantity sold exceeds the current stock for this product.'],
                    ),
                    $this->failureRow(
                        $today,
                        'BEV-FIVEALIVE-1L',
                        'Beverages',
                        'Five Alive Pulpy Orange Juice',
                        1500,
                        null,
                        null,
                        'Quantity was left blank on the offline sheet.',
                        ['The quantity sold field is required.'],
                    ),
                    $this->failureRow(
                        $today,
                        'TOI-TP-COL-120G',
                        'Toiletries',
                        'Colgate MaxFresh Toothpaste',
                        1650,
                        2,
                        2000,
                        'Cashier entered a rounded total instead of the exact amount.',
                        ['The total amount must match unit price multiplied by quantity sold.'],
                    ),
                ],
            ],
            [
                'batch_code' => 'SIB-DEMO-'.$today->format('Ymd').'-F',
                'original_file_name' => 'daily-sales-'.$today->format('Y-m-d').'-night-pending.csv',
                'uploaded_by' => 'sales_admin',
                'status' => SalesImportBatchStatus::UPLOADED,
                'uploaded_at' => $today->setTime(21, 5, 0)->toDateTimeString(),
                'notes' => 'Night shift template has been uploaded and is waiting for review before processing.',
                'sales_rows' => [],
                'failure_rows' => [],
            ],
        ];
    }

    protected function stockEntry(
        string $reference,
        int $daysAgo,
        int $quantityAdded,
        float|int $unitCostPrice,
        float|int $unitSellingPrice,
        string $createdBy,
        string $note,
    ): array {
        return [
            'reference' => $reference,
            'days_ago' => $daysAgo,
            'quantity_added' => $quantityAdded,
            'unit_cost_price' => $unitCostPrice,
            'unit_selling_price' => $unitSellingPrice,
            'created_by' => $createdBy,
            'note' => $note,
        ];
    }

    protected function salesRow(
        CarbonImmutable $date,
        string $sku,
        int $quantitySold,
        ?string $note = null,
    ): array {
        return [
            'date' => $date->toDateString(),
            'sku' => $sku,
            'quantity_sold' => $quantitySold,
            'note' => $note,
        ];
    }

    /**
     * @param  array<int, string>  $errorMessages
     */
    protected function failureRow(
        CarbonImmutable $date,
        string $productCode,
        string $category,
        string $productName,
        float|int $unitPrice,
        ?int $quantitySold,
        float|int|null $totalAmount,
        string $note,
        array $errorMessages,
    ): array {
        return [
            'raw_row' => [
                'date' => $date->toDateString(),
                'product_code' => $productCode,
                'category' => $category,
                'product_name' => $productName,
                'unit_price' => $unitPrice,
                'quantity_sold' => $quantitySold,
                'total_amount' => $totalAmount,
                'note' => $note,
            ],
            'error_messages' => $errorMessages,
        ];
    }
}
