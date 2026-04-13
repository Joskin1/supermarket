<?php

namespace Tests\Feature\Reporting;

use App\Actions\Reporting\RefreshAllSummariesAction;
use App\Models\Category;
use App\Models\Product;
use App\Models\SalesImportBatch;
use App\Models\SalesRecord;
use App\Services\SalesReportingService;
use App\Services\SalesTrendService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportingServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_daily_report_and_rankings_match_the_reporting_summaries(): void
    {
        [$cola, $rice] = $this->seedSummarySourceData();

        $service = app(SalesReportingService::class);

        $dailyReport = $service->dailyReport(CarbonImmutable::parse('2026-04-07'));

        $this->assertSame(5500.0, $dailyReport['totals']['total_sales_amount']);
        $this->assertSame(8, $dailyReport['totals']['total_quantity_sold']);
        $this->assertSame(3, $dailyReport['totals']['total_transactions_count']);

        $topProducts = $service->topProductsBySales(
            CarbonImmutable::parse('2026-04-07'),
            CarbonImmutable::parse('2026-04-13'),
        );

        $this->assertSame($rice->id, $topProducts->first()->product_id);
        $this->assertSame(5000.0, (float) $topProducts->first()->total_sales_amount);
        $this->assertSame($cola->id, $service->topProductsByQuantity(
            CarbonImmutable::parse('2026-04-07'),
            CarbonImmutable::parse('2026-04-13'),
        )->first()->product_id);
    }

    public function test_weekly_report_and_week_over_week_comparison_are_calculated_correctly(): void
    {
        $this->seedSummarySourceData();

        $service = app(SalesReportingService::class);

        $weeklyReport = $service->weeklyReport(
            CarbonImmutable::parse('2026-04-07'),
            CarbonImmutable::parse('2026-04-13'),
        );

        $this->assertSame(8000.0, $weeklyReport['totals']['total_sales_amount']);
        $this->assertSame(11, $weeklyReport['totals']['total_quantity_sold']);
        $this->assertSame(5, $weeklyReport['totals']['total_transactions_count']);
        $this->assertSame(1142.86, $weeklyReport['average_daily_sales']);
        $this->assertSame('2026-04-07', $weeklyReport['best_day']?->sales_date?->toDateString());

        $comparison = $service->weekOverWeekComparison(CarbonImmutable::parse('2026-04-07'));

        $this->assertSame(8000.0, $comparison['current']['total_sales_amount']);
        $this->assertSame(3000.0, $comparison['previous']['total_sales_amount']);
        $this->assertSame(166.67, $comparison['sales_amount_change_percentage']);
        $this->assertSame(120.0, $comparison['quantity_change_percentage']);
    }

    public function test_category_performance_is_ranked_correctly(): void
    {
        $this->seedSummarySourceData();

        $categories = app(SalesReportingService::class)->categoryPerformance(
            CarbonImmutable::parse('2026-04-07'),
            CarbonImmutable::parse('2026-04-13'),
        );

        $this->assertSame('Groceries', $categories->first()->category_snapshot);
        $this->assertSame(5000.0, (float) $categories->first()->total_sales_amount);
        $this->assertSame(62.5, $categories->first()->sales_share_percentage);
    }

    public function test_category_reporting_preserves_distinct_snapshots_when_category_ids_are_missing(): void
    {
        $batch = SalesImportBatch::factory()->processed()->create();
        $fallbackCategory = Category::factory()->create(['name' => 'Household']);

        $softDrinks = Product::factory()->create([
            'category_id' => $fallbackCategory->id,
            'name' => 'Orange Soda',
            'sku' => 'SKU-ORANGE-SODA',
            'selling_price' => 700,
        ]);

        $snacks = Product::factory()->create([
            'category_id' => $fallbackCategory->id,
            'name' => 'Plantain Chips',
            'sku' => 'SKU-PLANTAIN-CHIPS',
            'selling_price' => 900,
        ]);

        SalesRecord::factory()
            ->for($softDrinks, 'product')
            ->for($batch, 'batch')
            ->state([
                'product_code_snapshot' => $softDrinks->sku,
                'product_name_snapshot' => $softDrinks->name,
                'category_snapshot' => 'Soft Drinks',
                'unit_price' => 700,
                'quantity_sold' => 2,
                'total_amount' => 1400,
                'sales_date' => '2026-04-10',
            ])
            ->create();

        SalesRecord::factory()
            ->for($snacks, 'product')
            ->for($batch, 'batch')
            ->state([
                'product_code_snapshot' => $snacks->sku,
                'product_name_snapshot' => $snacks->name,
                'category_snapshot' => 'Snacks',
                'unit_price' => 900,
                'quantity_sold' => 3,
                'total_amount' => 2700,
                'sales_date' => '2026-04-10',
            ])
            ->create();

        app(RefreshAllSummariesAction::class)->fullRebuild();

        $categoryPerformance = app(SalesReportingService::class)->categoryPerformance(
            CarbonImmutable::parse('2026-04-10'),
            CarbonImmutable::parse('2026-04-10'),
        );

        $this->assertSame(['Snacks', 'Soft Drinks'], $categoryPerformance->pluck('category_snapshot')->all());
        $this->assertSame([2700.0, 1400.0], $categoryPerformance->map(
            fn (object $row): float => (float) $row->total_sales_amount,
        )->all());

        $distribution = app(SalesTrendService::class)->categoryDistribution(
            CarbonImmutable::parse('2026-04-10'),
            CarbonImmutable::parse('2026-04-10'),
        );

        $this->assertSame(['Snacks', 'Soft Drinks'], $distribution['labels']);
        $this->assertSame([2700.0, 1400.0], $distribution['values']);
    }

    /**
     * @return array{0: Product, 1: Product}
     */
    protected function seedSummarySourceData(): array
    {
        $beverages = Category::factory()->create(['name' => 'Beverages']);
        $groceries = Category::factory()->create(['name' => 'Groceries']);

        $cola = Product::factory()->create([
            'category_id' => $beverages->id,
            'name' => 'Cola',
            'sku' => 'SKU-COLA',
            'selling_price' => 500,
        ]);

        $rice = Product::factory()->create([
            'category_id' => $groceries->id,
            'name' => 'Rice',
            'sku' => 'SKU-RICE',
            'selling_price' => 1000,
        ]);

        $previousWeekBatch = SalesImportBatch::factory()->processed()->create();
        $currentWeekBatch = SalesImportBatch::factory()->processed()->create();

        $this->createSalesRecord($cola, $previousWeekBatch, '2026-04-02', 4, 500);
        $this->createSalesRecord($rice, $previousWeekBatch, '2026-04-03', 1, 1000);

        $this->createSalesRecord($cola, $currentWeekBatch, '2026-04-07', 2, 500);
        $this->createSalesRecord($cola, $currentWeekBatch, '2026-04-07', 3, 500);
        $this->createSalesRecord($rice, $currentWeekBatch, '2026-04-07', 3, 1000);
        $this->createSalesRecord($rice, $currentWeekBatch, '2026-04-09', 2, 1000);
        $this->createSalesRecord($cola, $currentWeekBatch, '2026-04-09', 1, 500);

        app(RefreshAllSummariesAction::class)->fullRebuild();

        return [$cola, $rice];
    }

    protected function createSalesRecord(
        Product $product,
        SalesImportBatch $batch,
        string $salesDate,
        int $quantitySold,
        float $unitPrice,
    ): SalesRecord {
        return SalesRecord::factory()
            ->for($product, 'product')
            ->for($batch, 'batch')
            ->state([
                'product_code_snapshot' => $product->sku,
                'product_name_snapshot' => $product->name,
                'category_snapshot' => $product->category?->name,
                'unit_price' => $unitPrice,
                'quantity_sold' => $quantitySold,
                'total_amount' => round($unitPrice * $quantitySold, 2),
                'sales_date' => $salesDate,
            ])
            ->create();
    }
}
