<?php

namespace Database\Seeders;

use App\Actions\Inventory\CreateStockEntryAction;
use App\Models\Category;
use App\Models\Product;
use App\Models\StockEntry;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class InventoryDevelopmentSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            SudoUserSeeder::class,
        ]);

        /** @var User $sudoUser */
        $sudoUser = User::query()
            ->where('email', env('SUDO_EMAIL', 'akinjoseph221@gmail.com'))
            ->firstOrFail();

        $categories = collect([
            [
                'name' => 'Cosmetics',
                'description' => 'Fragrances, body sprays, skincare products, and beauty essentials.',
            ],
            [
                'name' => 'Toiletries',
                'description' => 'Daily-use personal care products such as soap, toothpaste, and deodorant.',
            ],
            [
                'name' => 'Groceries',
                'description' => 'Packaged food staples, grains, and pantry essentials.',
            ],
            [
                'name' => 'Beverages',
                'description' => 'Soft drinks, bottled water, juices, and energy drinks.',
            ],
            [
                'name' => 'Household Items',
                'description' => 'Cleaning products and everyday home maintenance supplies.',
            ],
        ])->mapWithKeys(function (array $category): array {
            $record = Category::query()->updateOrCreate(
                ['slug' => Str::slug($category['name'])],
                [
                    'name' => $category['name'],
                    'description' => $category['description'],
                    'is_active' => true,
                ],
            );

            return [$record->name => $record];
        });

        $products = [
            [
                'category' => 'Cosmetics',
                'product_group' => 'Perfume',
                'name' => 'Zara Gold Perfume',
                'sku' => 'COS-PERF-ZARA-50',
                'brand' => 'Zara',
                'variant' => '50ml',
                'description' => 'A premium perfume often stocked near the checkout beauty section.',
                'purchase_price' => 18500,
                'selling_price' => 22500,
                'reorder_level' => 4,
                'unit_of_measure' => 'bottle',
            ],
            [
                'category' => 'Cosmetics',
                'product_group' => 'Body Spray',
                'name' => 'Classic Fresh Body Mist',
                'sku' => 'COS-BMIST-CLAS-100',
                'brand' => 'Classic',
                'variant' => '100ml',
                'description' => 'A fast-moving body mist popular with everyday shoppers.',
                'purchase_price' => 4200,
                'selling_price' => 5200,
                'reorder_level' => 6,
                'unit_of_measure' => 'bottle',
            ],
            [
                'category' => 'Toiletries',
                'product_group' => 'Roll-On',
                'name' => 'Nivea Men Roll-On',
                'sku' => 'TOI-ROLL-NIV-50',
                'brand' => 'Nivea',
                'variant' => '50ml',
                'description' => 'A standard deodorant line for the toiletries aisle.',
                'purchase_price' => 2600,
                'selling_price' => 3200,
                'reorder_level' => 8,
                'unit_of_measure' => 'pcs',
            ],
            [
                'category' => 'Toiletries',
                'product_group' => 'Toothpaste',
                'name' => 'Colgate MaxFresh Toothpaste',
                'sku' => 'TOI-TP-COL-120',
                'brand' => 'Colgate',
                'variant' => '120g',
                'description' => 'A common toothpaste line with strong daily sales volume.',
                'purchase_price' => 1300,
                'selling_price' => 1650,
                'reorder_level' => 10,
                'unit_of_measure' => 'pcs',
            ],
            [
                'category' => 'Groceries',
                'product_group' => 'Rice',
                'name' => 'Mama Gold Parboiled Rice',
                'sku' => 'GRO-RICE-MG-5KG',
                'brand' => 'Mama Gold',
                'variant' => '5kg',
                'description' => 'A bagged rice staple sold in the dry goods section.',
                'purchase_price' => 7700,
                'selling_price' => 8900,
                'reorder_level' => 5,
                'unit_of_measure' => 'bag',
            ],
            [
                'category' => 'Beverages',
                'product_group' => 'Soft Drink',
                'name' => 'Coca-Cola Classic Soft Drink',
                'sku' => 'BEV-COKE-50CL',
                'brand' => 'Coca-Cola',
                'variant' => '50cl',
                'description' => 'A chilled beverage staple stocked in packs throughout the day.',
                'purchase_price' => 520,
                'selling_price' => 650,
                'reorder_level' => 24,
                'unit_of_measure' => 'bottle',
            ],
            [
                'category' => 'Household Items',
                'product_group' => 'Detergent',
                'name' => 'Ariel Ultra Clean Detergent',
                'sku' => 'HOU-DET-ARL-850',
                'brand' => 'Ariel',
                'variant' => '850g',
                'description' => 'A laundry detergent line for household restocking.',
                'purchase_price' => 3400,
                'selling_price' => 4200,
                'reorder_level' => 7,
                'unit_of_measure' => 'pack',
            ],
        ];

        foreach ($products as $product) {
            /** @var Category $category */
            $category = $categories[$product['category']];

            Product::query()->updateOrCreate(
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
                    'is_active' => true,
                ],
            );
        }

        $stockEntries = [
            [
                'reference' => 'DEV-STOCK-0001',
                'sku' => 'COS-PERF-ZARA-50',
                'quantity_added' => 12,
                'unit_cost_price' => 18500,
                'unit_selling_price' => 22500,
                'stock_date' => now()->subDays(12)->toDateString(),
                'note' => 'Initial shelf stock for cosmetics launch display.',
            ],
            [
                'reference' => 'DEV-STOCK-0002',
                'sku' => 'COS-BMIST-CLAS-100',
                'quantity_added' => 18,
                'unit_cost_price' => 4200,
                'unit_selling_price' => 5200,
                'stock_date' => now()->subDays(10)->toDateString(),
                'note' => 'Regular replenishment after weekend sales.',
            ],
            [
                'reference' => 'DEV-STOCK-0003',
                'sku' => 'TOI-ROLL-NIV-50',
                'quantity_added' => 9,
                'unit_cost_price' => 2600,
                'unit_selling_price' => 3200,
                'stock_date' => now()->subDays(9)->toDateString(),
                'note' => 'Roll-on restock for toiletries section.',
            ],
            [
                'reference' => 'DEV-STOCK-0004',
                'sku' => 'TOI-TP-COL-120',
                'quantity_added' => 6,
                'unit_cost_price' => 1300,
                'unit_selling_price' => 1650,
                'stock_date' => now()->subDays(7)->toDateString(),
                'note' => 'Below reorder level on purpose for testing low-stock views.',
            ],
            [
                'reference' => 'DEV-STOCK-0005',
                'sku' => 'GRO-RICE-MG-5KG',
                'quantity_added' => 14,
                'unit_cost_price' => 7700,
                'unit_selling_price' => 8900,
                'stock_date' => now()->subDays(6)->toDateString(),
                'note' => 'Bulk grocery delivery for the rice aisle.',
            ],
            [
                'reference' => 'DEV-STOCK-0006',
                'sku' => 'BEV-COKE-50CL',
                'quantity_added' => 48,
                'unit_cost_price' => 520,
                'unit_selling_price' => 650,
                'stock_date' => now()->subDays(4)->toDateString(),
                'note' => 'Beverage cooler replenishment.',
            ],
            [
                'reference' => 'DEV-STOCK-0007',
                'sku' => 'HOU-DET-ARL-850',
                'quantity_added' => 5,
                'unit_cost_price' => 3400,
                'unit_selling_price' => 4200,
                'stock_date' => now()->subDays(2)->toDateString(),
                'note' => 'Low stock sample for urgent household restock.',
            ],
        ];

        foreach ($stockEntries as $entry) {
            if (StockEntry::query()->where('reference', $entry['reference'])->exists()) {
                continue;
            }

            /** @var Product $product */
            $product = Product::query()
                ->where('sku', $entry['sku'])
                ->firstOrFail();

            app(CreateStockEntryAction::class)->execute([
                'product_id' => $product->id,
                'quantity_added' => $entry['quantity_added'],
                'unit_cost_price' => $entry['unit_cost_price'],
                'unit_selling_price' => $entry['unit_selling_price'],
                'stock_date' => $entry['stock_date'],
                'reference' => $entry['reference'],
                'note' => $entry['note'],
                'created_by' => $sudoUser->id,
                'update_product_prices' => true,
            ]);
        }
    }
}
