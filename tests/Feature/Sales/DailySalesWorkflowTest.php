<?php

namespace Tests\Feature\Sales;

use App\Actions\Sales\CreateSalesImportBatchAction;
use App\Actions\Sales\ProcessSalesImportAction;
use App\Enums\RoleEnum;
use App\Enums\SalesImportBatchStatus;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\Feature\Sales\Concerns\BuildsDailySalesWorkbook;
use Tests\TestCase;

class DailySalesWorkflowTest extends TestCase
{
    use BuildsDailySalesWorkbook;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
    }

    public function test_sales_import_batch_is_created_on_upload(): void
    {
        $this->seed(RoleSeeder::class);

        $admin = User::factory()->create();
        $admin->assignRole(RoleEnum::ADMIN->value);
        $product = $this->makeProduct(['sku' => 'SKU-001']);

        $batch = app(CreateSalesImportBatchAction::class)->execute([
            'file' => $this->makeSalesWorkbookUpload(
                [$this->salesEntryRow(['product_code' => $product->sku])],
                [$this->referenceRowForProduct($product)],
            ),
            'uploaded_by' => $admin->id,
            'notes' => 'Morning upload.',
        ]);

        $this->assertNotNull($batch->file_path);
        $this->assertSame($admin->id, $batch->uploaded_by);
        $this->assertSame('Morning upload.', $batch->notes);
    }

    public function test_valid_row_creates_sales_record_deducts_stock_and_persists_sale_time(): void
    {
        $admin = $this->makeAdmin(withConfirmedTwoFactor: true);
        $product = $this->makeProduct(['sku' => 'SKU-001', 'current_stock' => 10]);

        $batch = app(CreateSalesImportBatchAction::class)->execute([
            'file' => $this->makeSalesWorkbookUpload(
                [$this->salesEntryRow([
                    'date' => '2026-04-10',
                    'time' => '14:25',
                    'product_code' => $product->sku,
                    'quantity_sold' => 4,
                ])],
                [$this->referenceRowForProduct($product)],
            ),
            'uploaded_by' => $admin->id,
        ]);

        $processed = app(ProcessSalesImportAction::class)->execute($batch);
        $product->refresh();

        $this->assertSame(SalesImportBatchStatus::PROCESSED, $processed->status);
        $this->assertSame(1, $processed->successful_rows);
        $this->assertSame(0, $processed->failed_rows);
        $this->assertSame(6, $product->current_stock);

        $this->assertDatabaseHas('sales_records', [
            'batch_id' => $batch->id,
            'product_id' => $product->id,
            'quantity_sold' => 4,
            'sales_date' => '2026-04-10',
            'sales_time' => '14:25:00',
            'source_row_number' => 2,
        ]);
    }

    public function test_total_amount_is_derived_from_unit_price_and_quantity_on_import(): void
    {
        $admin = $this->makeAdmin();
        $product = $this->makeProduct(['sku' => 'SKU-001', 'current_stock' => 10]);

        $batch = app(CreateSalesImportBatchAction::class)->execute([
            'file' => $this->makeSalesWorkbookUpload(
                [$this->salesEntryRow([
                    'product_code' => $product->sku,
                    'unit_price' => 575,
                    'quantity_sold' => 3,
                    'total_amount' => 9999,
                ])],
                [$this->referenceRowForProduct($product)],
            ),
            'uploaded_by' => $admin->id,
        ]);

        app(ProcessSalesImportAction::class)->execute($batch);

        $this->assertDatabaseHas('sales_records', [
            'batch_id' => $batch->id,
            'product_id' => $product->id,
            'total_amount' => 1725,
        ]);
    }

    public function test_repeated_product_rows_are_processed_as_separate_sales_when_stock_is_sufficient(): void
    {
        $admin = $this->makeAdmin();
        $product = $this->makeProduct(['sku' => 'SKU-001', 'current_stock' => 10]);

        $batch = app(CreateSalesImportBatchAction::class)->execute([
            'file' => $this->makeSalesWorkbookUpload(
                [
                    $this->salesEntryRow([
                        'time' => '09:00',
                        'product_code' => $product->sku,
                        'quantity_sold' => 3,
                    ]),
                    $this->salesEntryRow([
                        'time' => '09:30',
                        'product_code' => $product->sku,
                        'quantity_sold' => 2,
                    ]),
                ],
                [$this->referenceRowForProduct($product)],
            ),
            'uploaded_by' => $admin->id,
        ]);

        $processed = app(ProcessSalesImportAction::class)->execute($batch);
        $product->refresh();

        $this->assertSame(SalesImportBatchStatus::PROCESSED, $processed->status);
        $this->assertSame(2, $processed->successful_rows);
        $this->assertSame(0, $processed->failed_rows);
        $this->assertSame(5, $product->current_stock);
        $this->assertSame(5, $processed->total_quantity_sold);
        $this->assertDatabaseHas('sales_records', [
            'batch_id' => $batch->id,
            'source_row_number' => 2,
            'quantity_sold' => 3,
        ]);
        $this->assertDatabaseHas('sales_records', [
            'batch_id' => $batch->id,
            'source_row_number' => 3,
            'quantity_sold' => 2,
        ]);
    }

    public function test_running_stock_validation_respects_cumulative_row_order_within_the_batch(): void
    {
        $admin = $this->makeAdmin();
        $product = $this->makeProduct(['sku' => 'SKU-001', 'current_stock' => 4]);

        $batch = app(CreateSalesImportBatchAction::class)->execute([
            'file' => $this->makeSalesWorkbookUpload(
                [
                    $this->salesEntryRow([
                        'time' => '09:00',
                        'product_code' => $product->sku,
                        'quantity_sold' => 3,
                    ]),
                    $this->salesEntryRow([
                        'time' => '09:30',
                        'product_code' => $product->sku,
                        'quantity_sold' => 2,
                    ]),
                ],
                [$this->referenceRowForProduct($product)],
            ),
            'uploaded_by' => $admin->id,
        ]);

        $processed = app(ProcessSalesImportAction::class)->execute($batch);
        $product->refresh();

        $this->assertSame(SalesImportBatchStatus::PROCESSED_WITH_FAILURES, $processed->status);
        $this->assertSame(1, $processed->successful_rows);
        $this->assertSame(1, $processed->failed_rows);
        $this->assertSame(1, $product->current_stock);

        $this->assertDatabaseHas('sales_import_failures', [
            'batch_id' => $batch->id,
            'row_number' => 3,
            'product_code' => $product->sku,
        ]);
    }

    public function test_blank_sales_entry_rows_are_skipped_cleanly(): void
    {
        $admin = $this->makeAdmin();
        $product = $this->makeProduct(['sku' => 'SKU-001', 'current_stock' => 8]);

        $batch = app(CreateSalesImportBatchAction::class)->execute([
            'file' => $this->makeSalesWorkbookUpload(
                [
                    $this->salesEntryRow([
                        'product_code' => $product->sku,
                        'quantity_sold' => 2,
                    ]),
                    [
                        'date' => now()->toDateString(),
                        'time' => '',
                        'product_code' => '',
                        'product_name' => '',
                        'unit_price' => '',
                        'quantity_sold' => '',
                        'total_amount' => '',
                        'note' => '',
                    ],
                ],
                [$this->referenceRowForProduct($product)],
            ),
            'uploaded_by' => $admin->id,
        ]);

        $processed = app(ProcessSalesImportAction::class)->execute($batch);

        $this->assertSame(SalesImportBatchStatus::PROCESSED, $processed->status);
        $this->assertSame(1, $processed->total_rows);
        $this->assertSame(1, $processed->successful_rows);
        $this->assertSame(0, $processed->failed_rows);
    }

    public function test_partially_filled_invalid_rows_create_failures(): void
    {
        $admin = $this->makeAdmin();
        $product = $this->makeProduct(['sku' => 'SKU-001', 'current_stock' => 5]);

        $batch = app(CreateSalesImportBatchAction::class)->execute([
            'file' => $this->makeSalesWorkbookUpload(
                [
                    $this->salesEntryRow([
                        'product_code' => $product->sku,
                        'unit_price' => 500,
                        'quantity_sold' => '',
                        'total_amount' => '',
                    ]),
                ],
                [$this->referenceRowForProduct($product)],
            ),
            'uploaded_by' => $admin->id,
        ]);

        $processed = app(ProcessSalesImportAction::class)->execute($batch);
        $product->refresh();

        $this->assertSame(SalesImportBatchStatus::FAILED, $processed->status);
        $this->assertSame(0, $processed->successful_rows);
        $this->assertSame(1, $processed->failed_rows);
        $this->assertSame(5, $product->current_stock);
        $this->assertDatabaseCount('sales_import_failures', 1);
    }

    public function test_unknown_product_code_fails_and_does_not_deduct_stock(): void
    {
        $admin = $this->makeAdmin();
        $product = $this->makeProduct(['sku' => 'SKU-001', 'current_stock' => 5]);

        $batch = app(CreateSalesImportBatchAction::class)->execute([
            'file' => $this->makeSalesWorkbookUpload([
                $this->salesEntryRow(['product_code' => 'UNKNOWN-001']),
            ]),
            'uploaded_by' => $admin->id,
        ]);

        $processed = app(ProcessSalesImportAction::class)->execute($batch);
        $product->refresh();

        $this->assertSame(SalesImportBatchStatus::FAILED, $processed->status);
        $this->assertSame(0, $processed->successful_rows);
        $this->assertSame(1, $processed->failed_rows);
        $this->assertSame(5, $product->current_stock);
        $this->assertDatabaseCount('sales_records', 0);
        $this->assertDatabaseCount('sales_import_failures', 1);
    }

    public function test_duplicate_file_detection_blocks_reimport(): void
    {
        $admin = $this->makeAdmin();
        $product = $this->makeProduct(['sku' => 'SKU-001', 'current_stock' => 10]);

        $binary = $this->buildSalesWorkbookBinary(
            [$this->salesEntryRow(['product_code' => $product->sku])],
            [$this->referenceRowForProduct($product)],
        );

        $firstBatch = app(CreateSalesImportBatchAction::class)->execute([
            'file' => UploadedFile::fake()->createWithContent('daily-sales.xlsx', $binary),
            'uploaded_by' => $admin->id,
        ]);

        app(ProcessSalesImportAction::class)->execute($firstBatch);

        $this->expectException(ValidationException::class);

        app(CreateSalesImportBatchAction::class)->execute([
            'file' => UploadedFile::fake()->createWithContent('daily-sales.xlsx', $binary),
            'uploaded_by' => $admin->id,
        ]);
    }

    public function test_batch_totals_are_computed_correctly(): void
    {
        $admin = $this->makeAdmin();
        $product = $this->makeProduct(['sku' => 'SKU-001', 'current_stock' => 10]);

        $batch = app(CreateSalesImportBatchAction::class)->execute([
            'file' => $this->makeSalesWorkbookUpload(
                [
                    $this->salesEntryRow(['product_code' => $product->sku, 'quantity_sold' => 2, 'unit_price' => 500]),
                    $this->salesEntryRow(['product_code' => $product->sku, 'quantity_sold' => 1, 'unit_price' => 500]),
                ],
                [$this->referenceRowForProduct($product)],
            ),
            'uploaded_by' => $admin->id,
        ]);

        $processed = app(ProcessSalesImportAction::class)->execute($batch);

        $this->assertSame(2, $processed->total_rows);
        $this->assertSame(2, $processed->successful_rows);
        $this->assertSame(0, $processed->failed_rows);
        $this->assertSame(3, $processed->total_quantity_sold);
        $this->assertSame('1500.00', $processed->total_sales_amount);
    }

    public function test_sales_import_batches_are_accessible_to_admin_users(): void
    {
        $admin = $this->makeAdmin(withConfirmedTwoFactor: true);

        $this->actingAs($admin);

        $this->get('/admin/sales-import-batches')
            ->assertOk();

        $this->get('/admin/daily-sales-export')
            ->assertOk()
            ->assertSeeText('Sales Entry Log');
    }

    public function test_sales_import_batches_are_accessible_to_sudo_users(): void
    {
        $sudo = $this->makeSudo(array_merge(
            ['email_verified_at' => now()],
            $this->confirmedTwoFactorAttributes(),
        ));

        $this->actingAs($sudo);

        $this->get('/admin/sales-import-batches')
            ->assertOk();

        $this->get('/admin/daily-sales-export')
            ->assertOk();
    }

    public function test_users_without_roles_cannot_access_sales_imports(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $this->get('/admin/sales-import-batches')
            ->assertForbidden();

        $this->get('/admin/daily-sales-export')
            ->assertForbidden();
    }

    private function makeAdmin(bool $withConfirmedTwoFactor = false): User
    {
        $this->seed(RoleSeeder::class);

        $attributes = $withConfirmedTwoFactor
            ? array_merge(['email_verified_at' => now()], $this->confirmedTwoFactorAttributes())
            : [];

        $admin = User::factory()->create($attributes);
        $admin->assignRole(RoleEnum::ADMIN->value);

        return $admin;
    }

    private function makeProduct(array $overrides = []): Product
    {
        return Product::query()->create(array_merge([
            'category_id' => Category::factory()->create(['name' => 'Beverages'])->id,
            'product_group' => 'Soft Drink',
            'name' => 'Coca-Cola Classic Soft Drink',
            'slug' => 'coca-cola-classic-soft-drink-50cl',
            'sku' => 'SKU-001',
            'brand' => 'Coca-Cola',
            'variant' => '50cl',
            'purchase_price' => 400,
            'selling_price' => 500,
            'current_stock' => 0,
            'reorder_level' => 3,
            'unit_of_measure' => 'bottle',
            'is_active' => true,
        ], $overrides));
    }
}
