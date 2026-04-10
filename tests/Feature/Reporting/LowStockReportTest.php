<?php

namespace Tests\Feature\Reporting;

use App\Models\Category;
use App\Models\Product;
use App\Services\LowStockReportingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LowStockReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_low_stock_and_out_of_stock_products_are_identified_correctly(): void
    {
        $category = Category::factory()->create(['name' => 'Beverages']);

        $lowStock = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Malt Drink',
            'sku' => 'SKU-MALT',
            'current_stock' => 2,
            'reorder_level' => 5,
        ]);

        $outOfStock = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Orange Soda',
            'sku' => 'SKU-SODA',
            'current_stock' => 0,
            'reorder_level' => 4,
        ]);

        Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Water',
            'sku' => 'SKU-WATER',
            'current_stock' => 12,
            'reorder_level' => 4,
        ]);

        $service = app(LowStockReportingService::class);

        $this->assertSame([$lowStock->id], $service->getLowStockProducts()->pluck('id')->all());
        $this->assertSame([$outOfStock->id], $service->getOutOfStockProducts()->pluck('id')->all());
        $this->assertSame([
            'total_products' => 3,
            'healthy_products' => 1,
            'low_stock_products' => 1,
            'out_of_stock_products' => 1,
        ], $service->getStockHealthSummary());
    }

    public function test_category_stock_risk_is_ranked_with_the_most_critical_category_first(): void
    {
        $beverages = Category::factory()->create(['name' => 'Beverages']);
        $groceries = Category::factory()->create(['name' => 'Groceries']);

        Product::factory()->create([
            'category_id' => $beverages->id,
            'current_stock' => 0,
            'reorder_level' => 3,
        ]);
        Product::factory()->create([
            'category_id' => $beverages->id,
            'current_stock' => 2,
            'reorder_level' => 4,
        ]);
        Product::factory()->create([
            'category_id' => $groceries->id,
            'current_stock' => 1,
            'reorder_level' => 6,
        ]);

        $riskRows = app(LowStockReportingService::class)->getCategoryStockRisk();

        $this->assertSame('Beverages', $riskRows->first()->category_name);
        $this->assertSame(1, $riskRows->first()->out_of_stock_products_count);
        $this->assertSame(1, $riskRows->first()->low_stock_products_count);
        $this->assertSame('Groceries', $riskRows->last()->category_name);
    }
}
