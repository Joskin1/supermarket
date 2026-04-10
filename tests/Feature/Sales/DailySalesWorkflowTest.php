<?php

namespace Tests\Feature\Sales;

use App\Actions\Sales\CreateSalesImportBatchAction;
use App\Actions\Sales\ProcessSalesImportAction;
use App\Enums\RoleEnum;
use App\Enums\SalesImportBatchStatus;
use App\Exports\DailySalesTemplateExport;
use App\Filament\Pages\DailySalesExport;
use App\Filament\Resources\SalesImportBatches\SalesImportBatchResource;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Support\SalesImport\DailySalesTemplateColumns;
use Database\Seeders\RoleSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class DailySalesWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_daily_sales_template_export_has_expected_columns(): void
    {
        $export = new DailySalesTemplateExport;

        $this->assertSame(DailySalesTemplateColumns::all(), $export->headings());
    }

    public function test_sales_import_batch_is_created_on_upload(): void
    {
        $this->seed(RoleSeeder::class);

        $admin = User::factory()->create();
        $admin->assignRole(RoleEnum::ADMIN->value);

        $batch = app(CreateSalesImportBatchAction::class)->execute([
            'file' => $this->makeCsvUpload([
                $this->validRow(['product_code' => 'SKU-001']),
            ]),
            'uploaded_by' => $admin->id,
            'notes' => 'Morning upload.',
        ]);

        $this->assertNotNull($batch->file_path);
        $this->assertSame($admin->id, $batch->uploaded_by);
        $this->assertSame('Morning upload.', $batch->notes);
    }

    public function test_valid_row_creates_sales_record_and_deducts_stock(): void
    {
        $admin = $this->makeAdmin();
        $product = $this->makeProduct(['sku' => 'SKU-001', 'current_stock' => 10]);

        $batch = app(CreateSalesImportBatchAction::class)->execute([
            'file' => $this->makeCsvUpload([
                $this->validRow(['product_code' => $product->sku, 'quantity_sold' => 4]),
            ]),
            'uploaded_by' => $admin->id,
        ]);

        $processed = app(ProcessSalesImportAction::class)->execute($batch);
        $product->refresh();

        $this->assertSame(SalesImportBatchStatus::PROCESSED, $processed->status);
        $this->assertSame(1, $processed->successful_rows);
        $this->assertSame(0, $processed->failed_rows);
        $this->assertSame(6, $product->current_stock);
        $this->assertDatabaseCount('sales_records', 1);
    }

    public function test_unknown_product_code_fails_and_does_not_deduct_stock(): void
    {
        $admin = $this->makeAdmin();
        $product = $this->makeProduct(['sku' => 'SKU-001', 'current_stock' => 5]);

        $batch = app(CreateSalesImportBatchAction::class)->execute([
            'file' => $this->makeCsvUpload([
                $this->validRow(['product_code' => 'UNKNOWN-001']),
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

    public function test_invalid_quantity_fails_and_does_not_deduct_stock(): void
    {
        $admin = $this->makeAdmin();
        $product = $this->makeProduct(['sku' => 'SKU-001', 'current_stock' => 5]);

        $batch = app(CreateSalesImportBatchAction::class)->execute([
            'file' => $this->makeCsvUpload([
                $this->validRow(['product_code' => $product->sku, 'quantity_sold' => 0]),
            ]),
            'uploaded_by' => $admin->id,
        ]);

        $processed = app(ProcessSalesImportAction::class)->execute($batch);
        $product->refresh();

        $this->assertSame(SalesImportBatchStatus::FAILED, $processed->status);
        $this->assertSame(5, $product->current_stock);
        $this->assertDatabaseCount('sales_records', 0);
        $this->assertDatabaseCount('sales_import_failures', 1);
    }

    public function test_insufficient_stock_fails_and_does_not_deduct_stock(): void
    {
        $admin = $this->makeAdmin();
        $product = $this->makeProduct(['sku' => 'SKU-001', 'current_stock' => 2]);

        $batch = app(CreateSalesImportBatchAction::class)->execute([
            'file' => $this->makeCsvUpload([
                $this->validRow(['product_code' => $product->sku, 'quantity_sold' => 5]),
            ]),
            'uploaded_by' => $admin->id,
        ]);

        $processed = app(ProcessSalesImportAction::class)->execute($batch);
        $product->refresh();

        $this->assertSame(SalesImportBatchStatus::FAILED, $processed->status);
        $this->assertSame(2, $product->current_stock);
        $this->assertDatabaseCount('sales_records', 0);
        $this->assertDatabaseCount('sales_import_failures', 1);
    }

    public function test_mixed_valid_and_invalid_rows_are_processed_with_failures(): void
    {
        $admin = $this->makeAdmin();
        $product = $this->makeProduct(['sku' => 'SKU-001', 'current_stock' => 8]);

        $batch = app(CreateSalesImportBatchAction::class)->execute([
            'file' => $this->makeCsvUpload([
                $this->validRow(['product_code' => $product->sku, 'quantity_sold' => 2]),
                $this->validRow(['product_code' => 'UNKNOWN-001']),
            ]),
            'uploaded_by' => $admin->id,
        ]);

        $processed = app(ProcessSalesImportAction::class)->execute($batch);
        $product->refresh();

        $this->assertSame(SalesImportBatchStatus::PROCESSED_WITH_FAILURES, $processed->status);
        $this->assertSame(2, $processed->total_rows);
        $this->assertSame(1, $processed->successful_rows);
        $this->assertSame(1, $processed->failed_rows);
        $this->assertSame(6, $product->current_stock);
        $this->assertDatabaseCount('sales_records', 1);
        $this->assertDatabaseCount('sales_import_failures', 1);
    }

    public function test_duplicate_file_detection_blocks_reimport(): void
    {
        $admin = $this->makeAdmin();
        $product = $this->makeProduct(['sku' => 'SKU-001', 'current_stock' => 10]);

        $file = $this->makeCsvUpload([
            $this->validRow(['product_code' => $product->sku, 'quantity_sold' => 1]),
        ]);

        $firstBatch = app(CreateSalesImportBatchAction::class)->execute([
            'file' => $file,
            'uploaded_by' => $admin->id,
        ]);

        app(ProcessSalesImportAction::class)->execute($firstBatch);

        $this->expectException(ValidationException::class);

        app(CreateSalesImportBatchAction::class)->execute([
            'file' => $file,
            'uploaded_by' => $admin->id,
        ]);
    }

    public function test_batch_totals_are_computed_correctly(): void
    {
        $admin = $this->makeAdmin();
        $product = $this->makeProduct(['sku' => 'SKU-001', 'current_stock' => 10]);

        $batch = app(CreateSalesImportBatchAction::class)->execute([
            'file' => $this->makeCsvUpload([
                $this->validRow(['product_code' => $product->sku, 'quantity_sold' => 2, 'unit_price' => 500]),
                $this->validRow(['product_code' => $product->sku, 'quantity_sold' => 1, 'unit_price' => 500]),
            ]),
            'uploaded_by' => $admin->id,
        ]);

        $processed = app(ProcessSalesImportAction::class)->execute($batch);

        $this->assertSame(2, $processed->total_rows);
        $this->assertSame(2, $processed->successful_rows);
        $this->assertSame(0, $processed->failed_rows);
        $this->assertSame(3, $processed->total_quantity_sold);
        $this->assertSame('1500.00', $processed->total_sales_amount);
    }

    public function test_sales_import_batches_are_accessible_to_admin_and_sudo(): void
    {
        $this->seed();

        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $admin = User::factory()->create();
        $admin->assignRole(RoleEnum::ADMIN->value);

        $sudo = User::query()
            ->where('email', env('SUDO_EMAIL', 'akinjoseph221@gmail.com'))
            ->firstOrFail();

        Filament::actingAs($admin);

        $this->get(SalesImportBatchResource::getUrl('index'))
            ->assertOk();

        $this->get(DailySalesExport::getUrl())
            ->assertOk();

        Filament::actingAs($sudo);

        $this->get(SalesImportBatchResource::getUrl('index'))
            ->assertOk();

        $this->get(DailySalesExport::getUrl())
            ->assertOk();
    }

    public function test_users_without_roles_cannot_access_sales_imports(): void
    {
        $user = User::factory()->create();

        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Filament::actingAs($user);

        $this->get(SalesImportBatchResource::getUrl('index'))
            ->assertForbidden();

        $this->get(DailySalesExport::getUrl())
            ->assertForbidden();
    }

    private function makeAdmin(): User
    {
        $this->seed(RoleSeeder::class);

        $admin = User::factory()->create();
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

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    private function makeCsvUpload(array $rows): UploadedFile
    {
        $content = implode("\n", array_map(function (array $row): string {
            return implode(',', array_map(function (mixed $value): string {
                $value = (string) $value;

                if (str_contains($value, ',') || str_contains($value, '"')) {
                    $value = '"'.str_replace('"', '""', $value).'"';
                }

                return $value;
            }, $row));
        }, array_merge([DailySalesTemplateColumns::all()], $rows)));

        return UploadedFile::fake()->createWithContent('daily-sales.csv', $content);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validRow(array $overrides = []): array
    {
        $row = array_merge([
            'date' => now()->toDateString(),
            'product_code' => 'SKU-001',
            'category' => 'Beverages',
            'product_name' => 'Coca-Cola Classic Soft Drink',
            'unit_price' => 500,
            'quantity_sold' => 1,
            'total_amount' => 500,
            'note' => '',
        ], $overrides);

        if (
            (array_key_exists('unit_price', $overrides) || array_key_exists('quantity_sold', $overrides))
            && ! array_key_exists('total_amount', $overrides)
        ) {
            $row['total_amount'] = (float) $row['unit_price'] * (int) $row['quantity_sold'];
        }

        return $row;
    }
}
