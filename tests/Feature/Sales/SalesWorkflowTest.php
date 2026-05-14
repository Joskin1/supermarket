<?php

namespace Tests\Feature\Sales;

use App\Actions\Reporting\RefreshAllSummariesAction;
use App\Actions\Sales\ApplySalesRecordToInventoryAction;
use App\Actions\Sales\CreateSalesImportBatchAction;
use App\Actions\Sales\ProcessSalesImportAction;
use App\Enums\SalesImportBatchStatus;
use App\Exports\DailySalesTemplateExport;
use App\Models\Product;
use App\Models\SalesImportBatch;
use App\Models\User;
use App\Support\SalesImport\DailySalesTemplateColumns;
use App\Support\SalesImport\SalesImportHeadingValidator;
use App\Support\SalesImport\SalesImportRowProcessor;
use App\Support\SalesImport\SalesImportRowValidator;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Style\Protection;
use RuntimeException;
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
            'barcode' => '012345678901',
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
                ?->rangeToArray('A1:E1', null, false, false, false)[0],
        );
        $this->assertSame(
            DailySalesTemplateColumns::salesEntryLog(),
            $spreadsheet->getSheetByName(DailySalesTemplateColumns::SALES_ENTRY_LOG_SHEET)
                ?->rangeToArray('A1:I1', null, false, false, false)[0],
        );
    }

    public function test_product_reference_sheet_contains_only_active_products(): void
    {
        $salesDate = CarbonImmutable::parse('2026-04-10');
        $activeProduct = Product::factory()->create([
            'sku' => 'SKU-ACTIVE-1001',
            'barcode' => '012345678901',
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
            ?->rangeToArray('A2:E10', null, false, false, false);

        $rows = array_values(array_filter($rows ?? [], static fn (array $row): bool => filled($row[0] ?? null)));

        $this->assertCount(1, $rows);
        $this->assertSame($activeProduct->barcode, $rows[0][0]);
        $this->assertSame($activeProduct->sku, $rows[0][1]);
        $this->assertSame($activeProduct->name, $rows[0][3]);
    }

    public function test_sales_entry_sheet_keeps_the_time_column_manual_and_uses_the_expected_formulas(): void
    {
        Product::factory()->create([
            'sku' => 'SKU-ACTIVE-1001',
            'barcode' => '012345678901',
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
        $this->assertSame('time', $sheet?->getCell('B1')->getValue());
        $this->assertNull($sheet?->getCell('B2')->getValue());

        // D2 (SKU) is a plain editable cell — no formula.
        $this->assertNull($sheet?->getCell('D2')->getValue());

        // E2 (product_name) and F2 (unit_price) have dual-lookup formulas.
        $this->assertStringStartsWith('=IF($C2<>', (string) $sheet?->getCell('E2')->getValue());
        $this->assertStringContainsString('MATCH($C2', (string) $sheet?->getCell('E2')->getValue());
        $this->assertStringContainsString('MATCH($D2', (string) $sheet?->getCell('E2')->getValue());
        $this->assertStringStartsWith('=IF($C2<>', (string) $sheet?->getCell('F2')->getValue());
        $this->assertStringContainsString('MATCH($C2', (string) $sheet?->getCell('F2')->getValue());
        $this->assertStringContainsString('MATCH($D2', (string) $sheet?->getCell('F2')->getValue());
        $this->assertStringStartsWith('=IF(OR($F2=', (string) $sheet?->getCell('H2')->getValue());

        // Worksheet protection is enabled.
        $this->assertTrue($sheet?->getProtection()->getSheet());

        // Locked columns: A (date), E (product_name), F (unit_price), H (total_amount).
        $this->assertSame(Protection::PROTECTION_PROTECTED, $sheet?->getStyle('A2')->getProtection()->getLocked());
        $this->assertSame(Protection::PROTECTION_PROTECTED, $sheet?->getStyle('E2')->getProtection()->getLocked());
        $this->assertSame(Protection::PROTECTION_PROTECTED, $sheet?->getStyle('F2')->getProtection()->getLocked());
        $this->assertSame(Protection::PROTECTION_PROTECTED, $sheet?->getStyle('H2')->getProtection()->getLocked());

        // Unlocked columns: B (time), C (barcode), D (sku), G (quantity), I (note).
        $this->assertSame(Protection::PROTECTION_UNPROTECTED, $sheet?->getStyle('B2')->getProtection()->getLocked());
        $this->assertSame(Protection::PROTECTION_UNPROTECTED, $sheet?->getStyle('C2')->getProtection()->getLocked());
        $this->assertSame(Protection::PROTECTION_UNPROTECTED, $sheet?->getStyle('D2')->getProtection()->getLocked());
        $this->assertSame(Protection::PROTECTION_UNPROTECTED, $sheet?->getStyle('G2')->getProtection()->getLocked());
        $this->assertSame(Protection::PROTECTION_UNPROTECTED, $sheet?->getStyle('I2')->getProtection()->getLocked());
        $this->assertStringContainsString('Ctrl+Shift+', (string) $sheet?->getCell('J2')->getValue());
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

    public function test_exported_template_workbook_can_be_uploaded_and_processed(): void
    {
        $uploader = User::factory()->create();
        $product = Product::factory()->create([
            'sku' => 'SKU-TEMPLATE-1001',
            'selling_price' => 2400,
            'current_stock' => 9,
            'is_active' => true,
        ]);

        $binary = Excel::raw(
            new DailySalesTemplateExport(CarbonImmutable::parse('2026-04-10')),
            \Maatwebsite\Excel\Excel::XLSX,
        );

        $spreadsheet = $this->loadSpreadsheetFromBinary($binary);
        $sheet = $spreadsheet->getSheetByName(DailySalesTemplateColumns::SALES_ENTRY_LOG_SHEET);
        $sheet?->setCellValue('B2', '10:45');
        $sheet?->setCellValue('C2', $product->barcode);
        $sheet?->setCellValue('G2', 2);

        $upload = UploadedFile::fake()->createWithContent(
            'daily-sales-template.xlsx',
            $this->saveSpreadsheetToBinary($spreadsheet),
        );

        $batch = app(CreateSalesImportBatchAction::class)->execute([
            'file' => $upload,
            'uploaded_by' => $uploader->id,
        ]);

        $processedBatch = app(ProcessSalesImportAction::class)->execute($batch);

        $this->assertSame(SalesImportBatchStatus::PROCESSED, $processedBatch->status);
        $this->assertSame(1, $processedBatch->successful_rows);
        $this->assertSame(0, $processedBatch->failed_rows);
        $this->assertSame(7, $product->fresh()->current_stock);
        $this->assertDatabaseHas('sales_records', [
            'batch_id' => $batch->id,
            'product_id' => $product->id,
            'quantity_sold' => 2,
            'unit_price' => 2400,
            'total_amount' => 4800,
            'sales_time' => '10:45:00',
        ]);
    }

    public function test_exported_template_round_trips_a_barcode_scanned_sale_without_manual_sku_or_price(): void
    {
        $uploader = User::factory()->create();
        $product = Product::factory()->create([
            'sku' => 'SKU-BARCODE-ROUNDTRIP',
            'barcode' => '5012345678900',
            'name' => 'Barcode Round Trip Product',
            'selling_price' => 1350,
            'current_stock' => 11,
            'is_active' => true,
        ]);

        $binary = Excel::raw(
            new DailySalesTemplateExport(CarbonImmutable::parse('2026-04-11')),
            \Maatwebsite\Excel\Excel::XLSX,
        );

        $spreadsheet = $this->loadSpreadsheetFromBinary($binary);
        $referenceSheet = $spreadsheet->getSheetByName(DailySalesTemplateColumns::PRODUCT_REFERENCE_SHEET);
        $entrySheet = $spreadsheet->getSheetByName(DailySalesTemplateColumns::SALES_ENTRY_LOG_SHEET);

        $this->assertSame($product->barcode, (string) $referenceSheet?->getCell('A2')->getValue());
        $this->assertSame($product->sku, $referenceSheet?->getCell('B2')->getValue());
        $this->assertSame($product->name, $referenceSheet?->getCell('D2')->getValue());
        $this->assertSame('2026-04-11', $entrySheet?->getCell('A2')->getFormattedValue());

        $entrySheet?->setCellValue('B2', '16:20');
        $entrySheet?->setCellValue('C2', $product->barcode);
        $entrySheet?->setCellValue('G2', 3);

        $upload = UploadedFile::fake()->createWithContent(
            'daily-sales-barcode-round-trip.xlsx',
            $this->saveSpreadsheetToBinary($spreadsheet),
        );

        $batch = app(CreateSalesImportBatchAction::class)->execute([
            'file' => $upload,
            'uploaded_by' => $uploader->id,
        ]);

        $processedBatch = app(ProcessSalesImportAction::class)->execute($batch);

        $this->assertSame(SalesImportBatchStatus::PROCESSED, $processedBatch->status);
        $this->assertSame(1, $processedBatch->successful_rows);
        $this->assertSame(0, $processedBatch->failed_rows);
        $this->assertSame(8, $product->fresh()->current_stock);

        $this->assertDatabaseHas('sales_records', [
            'batch_id' => $batch->id,
            'product_id' => $product->id,
            'product_code_snapshot' => $product->sku,
            'product_name_snapshot' => $product->name,
            'quantity_sold' => 3,
            'unit_price' => 1350,
            'total_amount' => 4050,
            'sales_date' => '2026-04-11',
            'sales_time' => '16:20:00',
            'source_row_number' => 2,
        ]);
    }

    public function test_exported_template_can_be_uploaded_with_manual_sku_when_barcode_is_missing(): void
    {
        $uploader = User::factory()->create();
        $product = Product::factory()->create([
            'sku' => 'SKU-MANUAL-1001',
            'selling_price' => 1900,
            'current_stock' => 6,
            'is_active' => true,
        ]);

        $binary = Excel::raw(
            new DailySalesTemplateExport(CarbonImmutable::parse('2026-04-10')),
            \Maatwebsite\Excel\Excel::XLSX,
        );

        $spreadsheet = $this->loadSpreadsheetFromBinary($binary);
        $sheet = $spreadsheet->getSheetByName(DailySalesTemplateColumns::SALES_ENTRY_LOG_SHEET);
        $sheet?->setCellValue('D2', $product->sku);
        $sheet?->setCellValue('G2', 2);

        $upload = UploadedFile::fake()->createWithContent(
            'daily-sales-template.xlsx',
            $this->saveSpreadsheetToBinary($spreadsheet),
        );

        $batch = app(CreateSalesImportBatchAction::class)->execute([
            'file' => $upload,
            'uploaded_by' => $uploader->id,
        ]);

        $processedBatch = app(ProcessSalesImportAction::class)->execute($batch);

        $this->assertSame(SalesImportBatchStatus::PROCESSED, $processedBatch->status);
        $this->assertSame(1, $processedBatch->successful_rows);
        $this->assertSame(4, $product->fresh()->current_stock);
        $this->assertDatabaseHas('sales_records', [
            'batch_id' => $batch->id,
            'product_id' => $product->id,
            'quantity_sold' => 2,
            'unit_price' => 1900,
            'total_amount' => 3800,
        ]);
    }

    public function test_exported_template_skips_untouched_rows_even_when_excel_caches_formula_values(): void
    {
        $uploader = User::factory()->create();
        $product = Product::factory()->create([
            'sku' => 'SKU-CACHED-1001',
            'selling_price' => 2400,
            'current_stock' => 9,
            'is_active' => true,
        ]);

        $binary = Excel::raw(
            new DailySalesTemplateExport(CarbonImmutable::parse('2026-04-10')),
            \Maatwebsite\Excel\Excel::XLSX,
        );

        $spreadsheet = $this->loadSpreadsheetFromBinary($binary);
        $sheet = $spreadsheet->getSheetByName(DailySalesTemplateColumns::SALES_ENTRY_LOG_SHEET);
        $sheet?->setCellValue('B2', '10:45');
        $sheet?->setCellValue('C2', $product->barcode);
        $sheet?->setCellValue('G2', 2);

        // Simulate stale cached formula values on untouched rows (E3, F3, H3 are formulas).
        // D3 is plain text now — no formula to cache.
        foreach (['E3', 'F3', 'H3'] as $coordinate) {
            $sheet?->getCell($coordinate)->setCalculatedValue(0);
        }

        $upload = UploadedFile::fake()->createWithContent(
            'daily-sales-template.xlsx',
            $this->saveSpreadsheetToBinary($spreadsheet),
        );

        $batch = app(CreateSalesImportBatchAction::class)->execute([
            'file' => $upload,
            'uploaded_by' => $uploader->id,
        ]);

        $processedBatch = app(ProcessSalesImportAction::class)->execute($batch);

        $this->assertSame(SalesImportBatchStatus::PROCESSED, $processedBatch->status);
        $this->assertSame(1, $processedBatch->successful_rows);
        $this->assertSame(0, $processedBatch->failed_rows);
    }

    public function test_import_still_processes_when_runtime_schema_is_missing_new_sales_columns(): void
    {
        $uploader = User::factory()->create();
        $product = Product::factory()->create([
            'sku' => 'SKU-COMPAT-1001',
            'selling_price' => 1800,
            'current_stock' => 12,
        ]);

        Schema::shouldReceive('getColumnListing')
            ->once()
            ->andReturn([
                'id',
                'batch_id',
                'product_id',
                'product_code_snapshot',
                'category_snapshot',
                'product_name_snapshot',
                'unit_price',
                'quantity_sold',
                'total_amount',
                'sales_date',
                'note',
                'created_by',
                'created_at',
                'updated_at',
            ]);

        $batch = app(CreateSalesImportBatchAction::class)->execute([
            'file' => $this->makeSalesWorkbookUpload(
                [
                    $this->salesEntryRow([
                        'date' => '2026-04-10',
                        'time' => '18:05',
                        'product_code' => $product->sku,
                        'product_name' => $product->name,
                        'unit_price' => 1800,
                        'quantity_sold' => 3,
                        'total_amount' => 5400,
                    ]),
                ],
                [$this->referenceRowForProduct($product)],
            ),
            'uploaded_by' => $uploader->id,
        ]);

        $processedBatch = app(ProcessSalesImportAction::class)->execute($batch);

        $this->assertSame(SalesImportBatchStatus::PROCESSED, $processedBatch->status);
        $this->assertSame(1, $processedBatch->successful_rows);
        $this->assertSame(0, $processedBatch->failed_rows);
        $this->assertDatabaseHas('sales_records', [
            'batch_id' => $batch->id,
            'product_id' => $product->id,
            'quantity_sold' => 3,
            'sales_date' => '2026-04-10',
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

    public function test_modified_workbook_for_an_already_imported_sales_date_is_blocked_before_stock_changes(): void
    {
        $uploader = User::factory()->create();
        $product = Product::factory()->create([
            'sku' => 'SKU-OVERLAP-1001',
            'selling_price' => 1750,
            'current_stock' => 8,
        ]);

        $firstBatch = app(CreateSalesImportBatchAction::class)->execute([
            'file' => $this->makeSalesWorkbookUpload(
                [
                    $this->salesEntryRow([
                        'date' => '2026-04-10',
                        'time' => '09:30',
                        'product_code' => $product->sku,
                        'product_name' => $product->name,
                        'unit_price' => 1750,
                        'quantity_sold' => 2,
                        'total_amount' => 3500,
                    ]),
                ],
                [$this->referenceRowForProduct($product)],
            ),
            'uploaded_by' => $uploader->id,
        ]);

        $processedFirstBatch = app(ProcessSalesImportAction::class)->execute($firstBatch);

        $this->assertSame(SalesImportBatchStatus::PROCESSED, $processedFirstBatch->status);
        $this->assertSame(6, $product->fresh()->current_stock);

        $secondBatch = app(CreateSalesImportBatchAction::class)->execute([
            'file' => $this->makeSalesWorkbookUpload(
                [
                    $this->salesEntryRow([
                        'date' => '2026-04-10',
                        'time' => '11:15',
                        'product_code' => $product->sku,
                        'product_name' => $product->name,
                        'unit_price' => 1750,
                        'quantity_sold' => 1,
                        'total_amount' => 1750,
                        'note' => 'Corrected workbook',
                    ]),
                ],
                [$this->referenceRowForProduct($product)],
            ),
            'uploaded_by' => $uploader->id,
        ]);

        $processedSecondBatch = app(ProcessSalesImportAction::class)->execute($secondBatch);

        $this->assertSame(SalesImportBatchStatus::FAILED, $processedSecondBatch->status);
        $this->assertStringContainsString('prevent duplicate stock deductions', (string) $processedSecondBatch->notes);
        $this->assertDatabaseCount('sales_records', 1);
        $this->assertSame(6, $product->fresh()->current_stock);
    }

    public function test_overlap_guard_ignores_untouched_template_rows_with_cached_formula_values(): void
    {
        $uploader = User::factory()->create();
        $product = Product::factory()->create([
            'sku' => 'SKU-OVERLAP-CACHED-1001',
            'selling_price' => 2400,
            'current_stock' => 9,
            'is_active' => true,
        ]);

        $existingBatch = app(CreateSalesImportBatchAction::class)->execute([
            'file' => $this->makeSalesWorkbookUpload(
                [
                    $this->salesEntryRow([
                        'date' => '2026-04-10',
                        'time' => '10:45',
                        'product_code' => $product->sku,
                        'product_name' => $product->name,
                        'unit_price' => 2400,
                        'quantity_sold' => 2,
                        'total_amount' => 4800,
                    ]),
                ],
                [$this->referenceRowForProduct($product)],
            ),
            'uploaded_by' => $uploader->id,
        ]);

        $this->assertSame(
            SalesImportBatchStatus::PROCESSED,
            app(ProcessSalesImportAction::class)->execute($existingBatch)->status,
        );

        $binary = Excel::raw(
            new DailySalesTemplateExport(CarbonImmutable::parse('2026-04-10')),
            \Maatwebsite\Excel\Excel::XLSX,
        );

        $spreadsheet = $this->loadSpreadsheetFromBinary($binary);
        $sheet = $spreadsheet->getSheetByName(DailySalesTemplateColumns::SALES_ENTRY_LOG_SHEET);

        // Only formula cells can have stale cached values — D is now plain text.
        foreach (['E2', 'F2', 'H2'] as $coordinate) {
            $sheet?->getCell($coordinate)->setCalculatedValue(0);
        }

        $emptyUpload = UploadedFile::fake()->createWithContent(
            'daily-sales-template.xlsx',
            $this->saveSpreadsheetToBinary($spreadsheet),
        );

        $emptyBatch = app(CreateSalesImportBatchAction::class)->execute([
            'file' => $emptyUpload,
            'uploaded_by' => $uploader->id,
        ]);

        $processedEmptyBatch = app(ProcessSalesImportAction::class)->execute($emptyBatch);

        $this->assertSame(SalesImportBatchStatus::FAILED, $processedEmptyBatch->status);
        $this->assertStringContainsString('did not contain any sales rows', (string) $processedEmptyBatch->notes);
        $this->assertStringNotContainsString('prevent duplicate stock deductions', (string) $processedEmptyBatch->notes);
        $this->assertDatabaseCount('sales_records', 1);
        $this->assertSame(7, $product->fresh()->current_stock);
    }

    public function test_fatal_import_failure_rolls_back_previously_imported_rows_and_stock_changes(): void
    {
        $uploader = User::factory()->create();
        $product = Product::factory()->create([
            'sku' => 'SKU-ROLLBACK-1001',
            'selling_price' => 1750,
            'current_stock' => 8,
        ]);

        $batch = app(CreateSalesImportBatchAction::class)->execute([
            'file' => $this->makeSalesWorkbookUpload(
                [
                    $this->salesEntryRow([
                        'date' => '2026-04-10',
                        'product_code' => $product->sku,
                        'product_name' => $product->name,
                        'unit_price' => 1750,
                        'quantity_sold' => 2,
                    ]),
                    $this->salesEntryRow([
                        'date' => '2026-04-10',
                        'product_code' => $product->sku,
                        'product_name' => $product->name,
                        'unit_price' => 1750,
                        'quantity_sold' => 1,
                    ]),
                ],
                [$this->referenceRowForProduct($product)],
            ),
            'uploaded_by' => $uploader->id,
        ]);

        $realRowProcessor = app(SalesImportRowProcessor::class);

        $crashingRowProcessor = new class($realRowProcessor) extends SalesImportRowProcessor
        {
            public function __construct(protected SalesImportRowProcessor $inner)
            {
                parent::__construct(
                    app(SalesImportRowValidator::class),
                    app(ApplySalesRecordToInventoryAction::class),
                );
            }

            protected int $calls = 0;

            public function process(SalesImportBatch $batch, array $row, int $rowNumber): void
            {
                $this->calls++;

                if ($this->calls === 2) {
                    throw new RuntimeException('Simulated fatal import crash.');
                }

                $this->inner->process($batch, $row, $rowNumber);
            }
        };

        $processedBatch = (new ProcessSalesImportAction(
            app(SalesImportHeadingValidator::class),
            $crashingRowProcessor,
            app(RefreshAllSummariesAction::class),
        ))->execute($batch);

        $this->assertSame(SalesImportBatchStatus::FAILED, $processedBatch->status);
        $this->assertSame(8, $product->fresh()->current_stock);
        $this->assertDatabaseCount('sales_records', 0);
        $this->assertDatabaseCount('sales_import_failures', 0);
    }
}
