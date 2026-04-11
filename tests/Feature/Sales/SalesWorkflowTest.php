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
use Maatwebsite\Excel\Facades\Excel;
use Tests\Feature\Sales\Concerns\BuildsDailySalesWorkbook;
use Tests\TestCase;

class SalesWorkflowTest extends TestCase
{
    use BuildsDailySalesWorkbook;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
    }

    public function test_daily_sales_template_export_produces_a_two_sheet_workbook(): void
    {
        Product::factory()->create([
            'sku' => 'SKU-ACTIVE-1001',
            'name' => 'Active Product',
            'is_active' => true,
        ]);

        $binary = Excel::raw(
            new DailySalesTemplateExport(CarbonImmutable::parse('2026-04-10')),
            \Maatwebsite\Excel\Excel::XLSX,
        );

        $spreadsheet = $this->loadSpreadsheetFromBinary($binary);

        $this->assertSame([
            DailySalesTemplateColumns::PRODUCT_REFERENCE_SHEET,
            DailySalesTemplateColumns::SALES_ENTRY_LOG_SHEET,
        ], $spreadsheet->getSheetNames());
        $this->assertSame(
            DailySalesTemplateColumns::productReference(),
            $spreadsheet->getSheetByName(DailySalesTemplateColumns::PRODUCT_REFERENCE_SHEET)
                ?->rangeToArray('A1:D1', null, false, false, false)[0],
        );
        $this->assertSame(
            DailySalesTemplateColumns::salesEntryLog(),
            $spreadsheet->getSheetByName(DailySalesTemplateColumns::SALES_ENTRY_LOG_SHEET)
                ?->rangeToArray('A1:H1', null, false, false, false)[0],
        );
    }

    public function test_product_reference_sheet_contains_only_active_products(): void
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

        $binary = Excel::raw(
            new DailySalesTemplateExport($salesDate),
            \Maatwebsite\Excel\Excel::XLSX,
        );

        $spreadsheet = $this->loadSpreadsheetFromBinary($binary);
        $rows = $spreadsheet->getSheetByName(DailySalesTemplateColumns::PRODUCT_REFERENCE_SHEET)
            ?->rangeToArray('A2:D10', null, false, false, false);

        $rows = array_values(array_filter($rows ?? [], static fn (array $row): bool => filled($row[0] ?? null)));

        $this->assertCount(1, $rows);
        $this->assertSame($activeProduct->sku, $rows[0][0]);
        $this->assertSame($activeProduct->name, $rows[0][2]);
    }

    public function test_sales_entry_sheet_uses_the_new_columns_and_derived_formulas(): void
    {
        Product::factory()->create([
            'sku' => 'SKU-ACTIVE-1001',
            'name' => 'Active Product',
            'selling_price' => 2500,
            'is_active' => true,
        ]);

        $binary = Excel::raw(
            new DailySalesTemplateExport(CarbonImmutable::parse('2026-04-10')),
            \Maatwebsite\Excel\Excel::XLSX,
        );

        $spreadsheet = $this->loadSpreadsheetFromBinary($binary);
        $sheet = $spreadsheet->getSheetByName(DailySalesTemplateColumns::SALES_ENTRY_LOG_SHEET);

        $this->assertSame('2026-04-10', $sheet?->getCell('A2')->getFormattedValue());
        $this->assertStringStartsWith('=IF($C2=', (string) $sheet?->getCell('D2')->getValue());
        $this->assertStringStartsWith('=IF($C2=', (string) $sheet?->getCell('E2')->getValue());
        $this->assertStringStartsWith('=IF(OR($E2=', (string) $sheet?->getCell('G2')->getValue());
    }

    public function test_sales_import_batch_is_created_and_processed_from_an_uploaded_workbook(): void
    {
        $uploader = User::factory()->create();
        $product = Product::factory()->create([
            'sku' => 'SKU-PROCESS-1001',
            'selling_price' => 2250,
            'current_stock' => 12,
        ]);

        $batch = app(CreateSalesImportBatchAction::class)->execute([
            'file' => $this->makeSalesWorkbookUpload(
                [
                    $this->salesEntryRow([
                        'date' => '2026-04-10',
                        'time' => '18:05',
                        'product_code' => $product->sku,
                        'product_name' => $product->name,
                        'unit_price' => 2250,
                        'quantity_sold' => 3,
                        'total_amount' => 6750,
                        'note' => 'Evening sales',
                    ]),
                ],
                [$this->referenceRowForProduct($product)],
            ),
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
            'sales_time' => '18:05:00',
        ]);
    }

    public function test_duplicate_file_detection_blocks_reimport_of_the_same_processed_file(): void
    {
        $uploader = User::factory()->create();
        $product = Product::factory()->create([
            'sku' => 'SKU-DUPE-1001',
            'selling_price' => 1750,
            'current_stock' => 8,
        ]);
        $fileContent = $this->buildSalesWorkbookBinary(
            [
                $this->salesEntryRow([
                    'date' => '2026-04-10',
                    'product_code' => $product->sku,
                    'product_name' => $product->name,
                    'unit_price' => 1750,
                    'quantity_sold' => 2,
                    'total_amount' => 3500,
                    'note' => '',
                ]),
            ],
            [$this->referenceRowForProduct($product)],
        );

        $firstBatch = app(CreateSalesImportBatchAction::class)->execute([
            'file' => UploadedFile::fake()->createWithContent('daily-sales.xlsx', $fileContent),
            'uploaded_by' => $uploader->id,
        ]);

        app(ProcessSalesImportAction::class)->execute($firstBatch);

        try {
            app(CreateSalesImportBatchAction::class)->execute([
                'file' => UploadedFile::fake()->createWithContent('daily-sales.xlsx', $fileContent),
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
}
