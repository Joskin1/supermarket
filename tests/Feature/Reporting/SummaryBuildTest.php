<?php

namespace Tests\Feature\Reporting;

use App\Actions\Reporting\RefreshAllSummariesAction;
use App\Models\Category;
use App\Models\Product;
use App\Models\SalesImportBatch;
use App\Models\SalesRecord;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SummaryBuildTest extends TestCase
{
    use RefreshDatabase;

    public function test_reporting_summaries_are_built_correctly_from_sales_records(): void
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

        $firstBatch = SalesImportBatch::factory()->processed()->create();
        $secondBatch = SalesImportBatch::factory()->processed()->create();

        $this->createSalesRecord($cola, $firstBatch, '2026-04-07', 2, 500);
        $this->createSalesRecord($cola, $firstBatch, '2026-04-07', 1, 500);
        $this->createSalesRecord($rice, $secondBatch, '2026-04-07', 3, 1000);
        $this->createSalesRecord($rice, $secondBatch, '2026-04-08', 2, 1000);

        app(RefreshAllSummariesAction::class)->execute(
            CarbonImmutable::parse('2026-04-07'),
            CarbonImmutable::parse('2026-04-08'),
        );

        $this->assertDatabaseHas('daily_sales_summaries', [
            'sales_date' => '2026-04-07',
            'total_transactions_count' => 3,
            'total_quantity_sold' => 6,
            'total_sales_amount' => 4500.00,
            'batches_count' => 2,
        ]);

        $this->assertDatabaseHas('daily_product_sales_summaries', [
            'sales_date' => '2026-04-07',
            'product_id' => $cola->id,
            'total_quantity_sold' => 3,
            'total_sales_amount' => 1500.00,
            'transactions_count' => 2,
        ]);

        $this->assertDatabaseHas('daily_category_sales_summaries', [
            'sales_date' => '2026-04-07',
            'category_snapshot' => 'Beverages',
            'total_quantity_sold' => 3,
            'total_sales_amount' => 1500.00,
            'transactions_count' => 2,
        ]);
    }

    public function test_summary_rebuild_is_idempotent_and_removes_stale_rows(): void
    {
        $category = Category::factory()->create(['name' => 'Beverages']);
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Orange Juice',
            'sku' => 'SKU-OJ',
            'selling_price' => 700,
        ]);
        $batch = SalesImportBatch::factory()->processed()->create();

        $record = $this->createSalesRecord($product, $batch, '2026-04-09', 2, 700);

        $action = app(RefreshAllSummariesAction::class);

        $this->assertSame(
            ['daily' => 1, 'products' => 1, 'categories' => 1],
            $action->forDate(CarbonImmutable::parse('2026-04-09')),
        );

        $this->assertSame(
            ['daily' => 1, 'products' => 1, 'categories' => 1],
            $action->forDate(CarbonImmutable::parse('2026-04-09')),
        );

        $this->assertDatabaseCount('daily_sales_summaries', 1);
        $this->assertDatabaseCount('daily_product_sales_summaries', 1);
        $this->assertDatabaseCount('daily_category_sales_summaries', 1);

        $record->delete();

        $this->assertSame(
            ['daily' => 0, 'products' => 0, 'categories' => 0],
            $action->forDate(CarbonImmutable::parse('2026-04-09')),
        );

        $this->assertDatabaseCount('daily_sales_summaries', 0);
        $this->assertDatabaseCount('daily_product_sales_summaries', 0);
        $this->assertDatabaseCount('daily_category_sales_summaries', 0);
    }

    public function test_reports_refresh_command_supports_date_range_and_validates_bad_input(): void
    {
        $category = Category::factory()->create(['name' => 'Groceries']);
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Beans',
            'sku' => 'SKU-BEANS',
            'selling_price' => 1200,
        ]);
        $batch = SalesImportBatch::factory()->processed()->create();

        $this->createSalesRecord($product, $batch, '2026-04-10', 1, 1200);

        $this->artisan('reports:refresh-summaries', [
            '--from' => '2026-04-10',
            '--to' => '2026-04-10',
        ])
            ->expectsOutputToContain('Refreshing reporting summaries')
            ->expectsOutputToContain('Reporting summaries refreshed successfully.')
            ->assertSuccessful();

        $this->assertDatabaseHas('daily_sales_summaries', [
            'sales_date' => '2026-04-10',
            'total_sales_amount' => 1200.00,
        ]);

        $this->artisan('reports:refresh-summaries', [
            '--date' => '10-04-2026',
        ])->assertFailed();
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
