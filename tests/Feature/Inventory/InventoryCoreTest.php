<?php

namespace Tests\Feature\Inventory;

use App\Actions\Inventory\CreateStockEntryAction;
use App\Models\Category;
use App\Models\Product;
use App\Models\StockEntry;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Tests\TestCase;

class InventoryCoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_can_be_created(): void
    {
        $category = Category::query()->create([
            'name' => 'Cosmetics',
            'description' => 'Beauty and fragrance products.',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Cosmetics',
            'slug' => 'cosmetics',
        ]);
    }

    public function test_product_can_be_created(): void
    {
        $category = $this->createCategory();

        $product = Product::query()->create([
            'category_id' => $category->id,
            'product_group' => 'Perfume',
            'name' => 'Zara Gold Perfume',
            'sku' => 'COS-PERF-ZARA-50',
            'brand' => 'Zara',
            'variant' => '50ml',
            'purchase_price' => 18500,
            'selling_price' => 22500,
            'reorder_level' => 4,
            'unit_of_measure' => 'bottle',
            'is_active' => true,
        ]);

        $product->refresh();

        $this->assertSame($category->id, $product->category->id);
        $this->assertSame(0, $product->current_stock);
        $this->assertSame('zara-gold-perfume-50ml', $product->slug);
    }

    public function test_duplicate_sku_is_rejected(): void
    {
        $category = $this->createCategory();

        Product::query()->create([
            'category_id' => $category->id,
            'product_group' => 'Perfume',
            'name' => 'Zara Gold Perfume',
            'sku' => 'COS-PERF-ZARA-50',
            'purchase_price' => 18500,
            'selling_price' => 22500,
            'reorder_level' => 4,
            'unit_of_measure' => 'bottle',
            'is_active' => true,
        ]);

        $this->expectException(QueryException::class);

        Product::query()->create([
            'category_id' => $category->id,
            'product_group' => 'Perfume',
            'name' => 'Zara Gold Perfume Restock',
            'sku' => 'COS-PERF-ZARA-50',
            'purchase_price' => 19000,
            'selling_price' => 23000,
            'reorder_level' => 4,
            'unit_of_measure' => 'bottle',
            'is_active' => true,
        ]);
    }

    public function test_stock_entry_increases_current_stock_and_can_update_product_prices(): void
    {
        $product = $this->createProduct([
            'current_stock' => 5,
            'purchase_price' => 1800,
            'selling_price' => 2200,
        ]);

        $stockEntry = app(CreateStockEntryAction::class)->execute([
            'product_id' => $product->id,
            'quantity_added' => 7,
            'unit_cost_price' => 1900,
            'unit_selling_price' => 2350,
            'stock_date' => '2026-04-10',
            'reference' => 'RESTOCK-1001',
            'update_product_prices' => true,
        ]);

        $product->refresh();

        $this->assertSame($product->id, $stockEntry->product_id);
        $this->assertSame(12, $product->current_stock);
        $this->assertSame('1900.00', $product->purchase_price);
        $this->assertSame('2350.00', $product->selling_price);
        $this->assertDatabaseHas('stock_entries', [
            'reference' => 'RESTOCK-1001',
            'product_id' => $product->id,
            'quantity_added' => 7,
        ]);
    }

    public function test_stock_entry_can_preserve_historical_prices_without_updating_product_defaults(): void
    {
        $product = $this->createProduct([
            'purchase_price' => 1200,
            'selling_price' => 1500,
        ]);

        $stockEntry = app(CreateStockEntryAction::class)->execute([
            'product_id' => $product->id,
            'quantity_added' => 4,
            'unit_cost_price' => 1300,
            'unit_selling_price' => 1600,
            'stock_date' => '2026-04-10',
            'reference' => 'RESTOCK-1002',
            'update_product_prices' => false,
        ]);

        $product->refresh();

        $this->assertSame('1200.00', $product->purchase_price);
        $this->assertSame('1500.00', $product->selling_price);
        $this->assertSame('1300.00', $stockEntry->unit_cost_price);
        $this->assertSame('1600.00', $stockEntry->unit_selling_price);
    }

    public function test_low_stock_logic_works(): void
    {
        $lowStockProduct = $this->createProduct([
            'current_stock' => 4,
            'reorder_level' => 5,
        ]);

        $healthyProduct = $this->createProduct([
            'sku' => 'BEV-COKE-50CL',
            'name' => 'Coca-Cola Classic Soft Drink',
            'brand' => 'Coca-Cola',
            'variant' => '50cl',
            'current_stock' => 30,
            'reorder_level' => 10,
            'unit_of_measure' => 'bottle',
        ]);

        $lowStockProductIds = Product::query()->lowStock()->pluck('id');

        $this->assertTrue($lowStockProductIds->contains($lowStockProduct->id));
        $this->assertFalse($lowStockProductIds->contains($healthyProduct->id));
        $this->assertTrue($lowStockProduct->isLowStock());
        $this->assertSame('low_stock', $lowStockProduct->stockStatus());
    }

    public function test_out_of_stock_logic_works(): void
    {
        $outOfStockProduct = $this->createProduct([
            'current_stock' => 0,
            'reorder_level' => 5,
        ]);

        $inStockProduct = $this->createProduct([
            'sku' => 'TOI-ROLL-NIV-50',
            'name' => 'Nivea Men Roll-On',
            'brand' => 'Nivea',
            'variant' => '50ml',
            'current_stock' => 6,
            'reorder_level' => 5,
        ]);

        $outOfStockIds = Product::query()->outOfStock()->pluck('id');

        $this->assertTrue($outOfStockIds->contains($outOfStockProduct->id));
        $this->assertFalse($outOfStockIds->contains($inStockProduct->id));
        $this->assertTrue($outOfStockProduct->isOutOfStock());
        $this->assertSame('out_of_stock', $outOfStockProduct->stockStatus());
    }

    public function test_stock_entry_creation_is_transactional(): void
    {
        $product = $this->createProduct([
            'current_stock' => 3,
        ]);

        $action = new class extends CreateStockEntryAction
        {
            protected function afterStockEntryCreated(StockEntry $stockEntry, Product $product, array $data): void
            {
                throw new RuntimeException('Simulated failure after stock entry creation.');
            }
        };

        try {
            $action->execute([
                'product_id' => $product->id,
                'quantity_added' => 5,
                'unit_cost_price' => 2000,
                'unit_selling_price' => 2400,
                'stock_date' => '2026-04-10',
                'reference' => 'ROLLBACK-0001',
            ]);

            $this->fail('The action should have thrown an exception.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Simulated failure after stock entry creation.', $exception->getMessage());
        }

        $product->refresh();

        $this->assertSame(3, $product->current_stock);
        $this->assertDatabaseCount('stock_entries', 0);
        $this->assertDatabaseMissing('stock_entries', [
            'reference' => 'ROLLBACK-0001',
        ]);
    }

    public function test_invalid_stock_entry_payload_is_rejected(): void
    {
        $this->expectException(ValidationException::class);

        app(CreateStockEntryAction::class)->execute([
            'product_id' => 999999,
            'quantity_added' => 0,
            'unit_cost_price' => -1,
            'unit_selling_price' => -1,
            'stock_date' => 'not-a-date',
        ]);
    }

    public function test_existing_product_can_receive_multiple_stock_entries_without_being_recreated(): void
    {
        $product = $this->createProduct([
            'current_stock' => 2,
        ]);

        $action = app(CreateStockEntryAction::class);

        $action->execute([
            'product_id' => $product->id,
            'quantity_added' => 5,
            'unit_cost_price' => 2000,
            'unit_selling_price' => 2500,
            'stock_date' => '2026-04-10',
            'reference' => 'RESTOCK-2001',
        ]);

        $action->execute([
            'product_id' => $product->id,
            'quantity_added' => 3,
            'unit_cost_price' => 2050,
            'unit_selling_price' => 2550,
            'stock_date' => '2026-04-11',
            'reference' => 'RESTOCK-2002',
        ]);

        $product->refresh();

        $this->assertDatabaseCount('products', 1);
        $this->assertDatabaseCount('stock_entries', 2);
        $this->assertSame(10, $product->current_stock);
        $this->assertSame([$product->id], StockEntry::query()->pluck('product_id')->unique()->values()->all());
    }

    private function createCategory(array $overrides = []): Category
    {
        return Category::factory()->create(array_merge([
            'description' => 'Beauty and fragrance products.',
            'is_active' => true,
        ], $overrides));
    }

    private function createProduct(array $overrides = []): Product
    {
        $category = $overrides['category'] ?? $this->createCategory(
            filled($overrides['category_name'] ?? null)
                ? ['name' => $overrides['category_name']]
                : [],
        );

        unset($overrides['category'], $overrides['category_name']);

        return Product::query()->create(array_merge([
            'category_id' => $category->id,
            'product_group' => 'Perfume',
            'name' => 'Zara Gold Perfume',
            'sku' => 'COS-PERF-ZARA-50',
            'brand' => 'Zara',
            'variant' => '50ml',
            'purchase_price' => 18500,
            'selling_price' => 22500,
            'current_stock' => 0,
            'reorder_level' => 4,
            'unit_of_measure' => 'bottle',
            'is_active' => true,
        ], $overrides));
    }
}
