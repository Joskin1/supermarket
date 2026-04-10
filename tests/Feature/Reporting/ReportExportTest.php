<?php

namespace Tests\Feature\Reporting;

use App\Actions\Reporting\RefreshAllSummariesAction;
use App\Exports\DailySalesReportExport;
use App\Exports\LowStockReportExport;
use App\Exports\TopProductsExport;
use App\Exports\WeeklySummaryExport;
use App\Models\Category;
use App\Models\Product;
use App\Models\SalesImportBatch;
use App\Models\SalesRecord;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_daily_sales_export_returns_expected_rows(): void
    {
        $this->seedExportSourceData();

        $exportRows = (new DailySalesReportExport(
            CarbonImmutable::parse('2026-04-07'),
            CarbonImmutable::parse('2026-04-07'),
        ))->collection();

        $this->assertSame('SKU-COLA', $exportRows->first()['product_code']);
        $this->assertSame(3, $exportRows->first()['quantity_sold']);
        $this->assertSame('Beverages', $exportRows->first()['category']);
    }

    public function test_weekly_summary_export_adds_a_total_row(): void
    {
        $this->seedExportSourceData();

        $rows = (new WeeklySummaryExport(
            CarbonImmutable::parse('2026-04-07'),
            CarbonImmutable::parse('2026-04-13'),
        ))->collection();

        $this->assertSame('Weekly Total', $rows->last()['date']);
        $this->assertSame(2, $rows->last()['transactions']);
        $this->assertSame(7, $rows->last()['quantity_sold']);
    }

    public function test_low_stock_and_top_performance_exports_respect_filters(): void
    {
        $category = Category::factory()->create(['name' => 'Beverages']);
        $cola = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Cola',
            'sku' => 'SKU-COLA',
            'current_stock' => 2,
            'reorder_level' => 5,
            'selling_price' => 500,
        ]);

        Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Water',
            'sku' => 'SKU-WATER',
            'current_stock' => 12,
            'reorder_level' => 4,
            'selling_price' => 300,
        ]);

        $batch = SalesImportBatch::factory()->processed()->create();
        SalesRecord::factory()
            ->for($cola, 'product')
            ->for($batch, 'batch')
            ->state([
                'product_code_snapshot' => $cola->sku,
                'product_name_snapshot' => $cola->name,
                'category_snapshot' => $category->name,
                'unit_price' => 500,
                'quantity_sold' => 5,
                'total_amount' => 2500,
                'sales_date' => '2026-04-07',
            ])
            ->create();

        app(RefreshAllSummariesAction::class)->fullRebuild();

        $lowStockRows = (new LowStockReportExport('low_stock'))->collection();
        $topRows = (new TopProductsExport(
            'products_revenue',
            CarbonImmutable::parse('2026-04-07'),
            CarbonImmutable::parse('2026-04-13'),
        ))->collection();

        $this->assertSame('SKU-COLA', $lowStockRows->first()['sku']);
        $this->assertSame('Cola', $topRows->first()['product_name']);
        $this->assertSame(2500.0, (float) $topRows->first()['sales_amount_ngn']);
    }

    /**
     * @return array{0: Product, 1: SalesImportBatch}
     */
    protected function seedExportSourceData(): array
    {
        $category = Category::factory()->create(['name' => 'Beverages']);
        $cola = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Cola',
            'sku' => 'SKU-COLA',
            'selling_price' => 500,
        ]);

        $batch = SalesImportBatch::factory()->processed()->create();

        SalesRecord::factory()
            ->for($cola, 'product')
            ->for($batch, 'batch')
            ->state([
                'product_code_snapshot' => $cola->sku,
                'product_name_snapshot' => $cola->name,
                'category_snapshot' => $category->name,
                'unit_price' => 500,
                'quantity_sold' => 3,
                'total_amount' => 1500,
                'sales_date' => '2026-04-07',
            ])
            ->create();

        SalesRecord::factory()
            ->for($cola, 'product')
            ->for($batch, 'batch')
            ->state([
                'product_code_snapshot' => $cola->sku,
                'product_name_snapshot' => $cola->name,
                'category_snapshot' => $category->name,
                'unit_price' => 500,
                'quantity_sold' => 4,
                'total_amount' => 2000,
                'sales_date' => '2026-04-08',
            ])
            ->create();

        app(RefreshAllSummariesAction::class)->fullRebuild();

        return [$cola, $batch];
    }
}
