<?php

namespace Tests\Feature\Sales;

use App\Actions\Sales\CreateSalesImportBatchAction;
use App\Actions\Sales\ProcessSalesImportAction;
use App\Enums\SalesImportBatchStatus;
use App\Exports\DailySalesTemplateExport;
use App\Models\Product;
use App\Models\User;
use App\Support\SalesImport\DailySalesTemplateColumns;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class SalesWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_daily_sales_template_export_uses_expected_columns_and_only_active_products(): void
    {
        $salesDate = CarbonImmutable::parse('2026-04-10');
        $activeProduct = Product::factory()->create([
            'sku' => 'SKU-ACTIVE-1001',
            'name' => 'Active Product',
            'selling_price' => 2500,
            'is_active' => true,
        ]);
        Product::factory()->create([
            'sku' => 'SKU-INACTIVE-1002',
            'name' => 'Inactive Product',
            'is_active' => false,
        ]);

        $export = new DailySalesTemplateExport($salesDate);
        $rows = $export->collection();

        $this->assertSame(DailySalesTemplateColumns::all(), $export->headings());
        $this->assertCount(1, $rows);
        $this->assertSame($salesDate->toDateString(), $rows->first()['date']);
        $this->assertSame($activeProduct->sku, $rows->first()['product_code']);
        $this->assertSame($activeProduct->name, $rows->first()['product_name']);
    }

    public function test_sales_import_batch_is_created_and_processed_from_an_uploaded_file(): void
    {
        Storage::fake('local');

        $uploader = User::factory()->create();
        $product = Product::factory()->create([
            'sku' => 'SKU-PROCESS-1001',
            'selling_price' => 2250,
            'current_stock' => 12,
        ]);

        $batch = app(CreateSalesImportBatchAction::class)->execute([
            'file' => $this->fakeSalesFile([
                [
                    'date' => '2026-04-10',
                    'product_code' => $product->sku,
                    'category' => $product->category?->name,
                    'product_name' => $product->name,
                    'unit_price' => '2250',
                    'quantity_sold' => '3',
                    'total_amount' => '6750',
                    'note' => 'Evening sales',
                ],
            ]),
            'uploaded_by' => $uploader->id,
        ]);

        Storage::disk('local')->assertExists($batch->file_path);

        $processedBatch = app(ProcessSalesImportAction::class)->execute($batch);

        $this->assertSame(SalesImportBatchStatus::PROCESSED, $processedBatch->status);
        $this->assertSame(1, $processedBatch->total_rows);
        $this->assertSame(1, $processedBatch->successful_rows);
        $this->assertSame(0, $processedBatch->failed_rows);
        $this->assertSame(3, $processedBatch->total_quantity_sold);
        $this->assertSame(6750.0, (float) $processedBatch->total_sales_amount);
        $this->assertSame(9, $product->fresh()->current_stock);

        $this->assertDatabaseHas('sales_records', [
            'batch_id' => $batch->id,
            'product_id' => $product->id,
            'quantity_sold' => 3,
            'sales_date' => '2026-04-10',
        ]);
    }

    public function test_prefilled_rows_without_quantities_are_skipped_without_creating_failures(): void
    {
        Storage::fake('local');

        $uploader = User::factory()->create();
        $soldProduct = Product::factory()->create([
            'sku' => 'SKU-SOLD-1001',
            'selling_price' => 1800,
            'current_stock' => 10,
        ]);
        $untouchedProduct = Product::factory()->create([
            'sku' => 'SKU-UNTOUCHED-1002',
            'selling_price' => 950,
            'current_stock' => 7,
        ]);

        $batch = app(CreateSalesImportBatchAction::class)->execute([
            'file' => $this->fakeSalesFile([
                [
                    'date' => '2026-04-10',
                    'product_code' => $soldProduct->sku,
                    'category' => $soldProduct->category?->name,
                    'product_name' => $soldProduct->name,
                    'unit_price' => '1800',
                    'quantity_sold' => '2',
                    'total_amount' => '3600',
                    'note' => '',
                ],
                [
                    'date' => '2026-04-10',
                    'product_code' => $untouchedProduct->sku,
                    'category' => $untouchedProduct->category?->name,
                    'product_name' => $untouchedProduct->name,
                    'unit_price' => '950',
                    'quantity_sold' => '',
                    'total_amount' => '',
                    'note' => '',
                ],
            ]),
            'uploaded_by' => $uploader->id,
        ]);

        $processedBatch = app(ProcessSalesImportAction::class)->execute($batch);

        $this->assertSame(SalesImportBatchStatus::PROCESSED, $processedBatch->status);
        $this->assertSame(1, $processedBatch->total_rows);
        $this->assertSame(1, $processedBatch->successful_rows);
        $this->assertSame(0, $processedBatch->failed_rows);
        $this->assertSame(8, $soldProduct->fresh()->current_stock);
        $this->assertSame(7, $untouchedProduct->fresh()->current_stock);
        $this->assertDatabaseCount('sales_records', 1);
        $this->assertDatabaseCount('sales_import_failures', 0);
    }

    public function test_mixed_valid_and_invalid_rows_process_valid_sales_and_record_failures(): void
    {
        Storage::fake('local');

        $uploader = User::factory()->create();
        $product = Product::factory()->create([
            'sku' => 'SKU-MIXED-1001',
            'selling_price' => 1400,
            'current_stock' => 10,
        ]);

        $batch = app(CreateSalesImportBatchAction::class)->execute([
            'file' => $this->fakeSalesFile([
                [
                    'date' => '2026-04-10',
                    'product_code' => $product->sku,
                    'category' => $product->category?->name,
                    'product_name' => $product->name,
                    'unit_price' => '1400',
                    'quantity_sold' => '2',
                    'total_amount' => '2800',
                    'note' => '',
                ],
                [
                    'date' => '2026-04-10',
                    'product_code' => 'SKU-UNKNOWN-4040',
                    'category' => 'Unknown',
                    'product_name' => 'Missing Product',
                    'unit_price' => '999',
                    'quantity_sold' => '1',
                    'total_amount' => '999',
                    'note' => '',
                ],
            ]),
            'uploaded_by' => $uploader->id,
        ]);

        $processedBatch = app(ProcessSalesImportAction::class)->execute($batch);

        $this->assertSame(SalesImportBatchStatus::PROCESSED_WITH_FAILURES, $processedBatch->status);
        $this->assertSame(2, $processedBatch->total_rows);
        $this->assertSame(1, $processedBatch->successful_rows);
        $this->assertSame(1, $processedBatch->failed_rows);
        $this->assertSame(8, $product->fresh()->current_stock);

        $this->assertDatabaseHas('sales_import_failures', [
            'batch_id' => $batch->id,
            'product_code' => 'SKU-UNKNOWN-4040',
        ]);
    }

    public function test_insufficient_stock_rows_are_recorded_as_failures_without_deducting_stock(): void
    {
        Storage::fake('local');

        $uploader = User::factory()->create();
        $product = Product::factory()->create([
            'sku' => 'SKU-STOCK-1001',
            'selling_price' => 3000,
            'current_stock' => 2,
        ]);

        $batch = app(CreateSalesImportBatchAction::class)->execute([
            'file' => $this->fakeSalesFile([
                [
                    'date' => '2026-04-10',
                    'product_code' => $product->sku,
                    'category' => $product->category?->name,
                    'product_name' => $product->name,
                    'unit_price' => '3000',
                    'quantity_sold' => '5',
                    'total_amount' => '15000',
                    'note' => '',
                ],
            ]),
            'uploaded_by' => $uploader->id,
        ]);

        $processedBatch = app(ProcessSalesImportAction::class)->execute($batch);

        $this->assertSame(SalesImportBatchStatus::FAILED, $processedBatch->status);
        $this->assertSame(1, $processedBatch->failed_rows);
        $this->assertSame(0, $processedBatch->successful_rows);
        $this->assertSame(2, $product->fresh()->current_stock);
        $this->assertDatabaseCount('sales_records', 0);
        $this->assertDatabaseHas('sales_import_failures', [
            'batch_id' => $batch->id,
            'product_code' => $product->sku,
        ]);
    }

    public function test_duplicate_file_detection_blocks_reimport_of_the_same_processed_file(): void
    {
        Storage::fake('local');

        $uploader = User::factory()->create();
        $product = Product::factory()->create([
            'sku' => 'SKU-DUPE-1001',
            'selling_price' => 1750,
            'current_stock' => 8,
        ]);
        $fileContent = $this->buildSalesCsv([
            [
                'date' => '2026-04-10',
                'product_code' => $product->sku,
                'category' => $product->category?->name,
                'product_name' => $product->name,
                'unit_price' => '1750',
                'quantity_sold' => '2',
                'total_amount' => '3500',
                'note' => '',
            ],
        ]);

        $firstBatch = app(CreateSalesImportBatchAction::class)->execute([
            'file' => UploadedFile::fake()->createWithContent('daily-sales.csv', $fileContent),
            'uploaded_by' => $uploader->id,
        ]);

        app(ProcessSalesImportAction::class)->execute($firstBatch);

        try {
            app(CreateSalesImportBatchAction::class)->execute([
                'file' => UploadedFile::fake()->createWithContent('daily-sales.csv', $fileContent),
                'uploaded_by' => $uploader->id,
            ]);

            $this->fail('Duplicate upload protection should have blocked the second import.');
        } catch (ValidationException $exception) {
            $this->assertStringContainsString(
                'already been imported',
                $exception->errors()['file'][0] ?? '',
            );
        }
    }

    protected function fakeSalesFile(array $rows): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('daily-sales.csv', $this->buildSalesCsv($rows));
    }

    /**
     * @param  array<int, array<string, string>>  $rows
     */
    protected function buildSalesCsv(array $rows): string
    {
        $columns = DailySalesTemplateColumns::all();
        $lines = [
            implode(',', $columns),
        ];

        foreach ($rows as $row) {
            $lines[] = implode(',', array_map(
                static fn (string $column): string => $row[$column] ?? '',
                $columns,
            ));
        }

        return implode(PHP_EOL, $lines).PHP_EOL;
    }
}
