<?php

namespace Tests\Feature;

use App\Actions\Inventory\CreateStockAdjustmentAction;
use App\Actions\Inventory\CreateStockEntryAction;
use App\Actions\Sales\CreateSalesImportBatchAction;
use App\Actions\Sales\ProcessSalesImportAction;
use App\Enums\RoleEnum;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Sales\Concerns\BuildsDailySalesWorkbook;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use BuildsDailySalesWorkbook;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
    }

    public function test_stock_operations_are_written_to_the_activity_log(): void
    {
        $actor = User::factory()->create();
        $product = Product::factory()->create([
            'current_stock' => 10,
        ]);

        app(CreateStockEntryAction::class)->execute([
            'product_id' => $product->id,
            'quantity_added' => 4,
            'unit_cost_price' => 1500,
            'unit_selling_price' => 1800,
            'stock_date' => '2026-04-13',
            'reference' => 'RESTOCK-3001',
            'created_by' => $actor->id,
        ]);

        app(CreateStockAdjustmentAction::class)->execute([
            'product_id' => $product->id,
            'adjustment_method' => 'quantity_change',
            'quantity_change' => -2,
            'reason' => 'Damaged items removed',
            'adjustment_date' => '2026-04-13',
            'adjusted_by' => $actor->id,
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'event' => 'stock_entry.created',
            'actor_id' => $actor->id,
        ]);
        $this->assertDatabaseHas('activity_logs', [
            'event' => 'stock_adjustment.created',
            'actor_id' => $actor->id,
        ]);
    }

    public function test_sales_import_upload_and_processing_are_written_to_the_activity_log(): void
    {
        $actor = User::factory()->create();
        $product = Product::factory()->create([
            'sku' => 'SKU-LOG-1001',
            'selling_price' => 2200,
            'current_stock' => 8,
        ]);

        $batch = app(CreateSalesImportBatchAction::class)->execute([
            'file' => $this->makeSalesWorkbookUpload(
                [
                    $this->salesEntryRow([
                        'date' => '2026-04-13',
                        'time' => '09:30',
                        'product_code' => $product->sku,
                        'product_name' => $product->name,
                        'unit_price' => 2200,
                        'quantity_sold' => 2,
                        'total_amount' => 4400,
                    ]),
                ],
                [$this->referenceRowForProduct($product)],
            ),
            'uploaded_by' => $actor->id,
        ]);

        app(ProcessSalesImportAction::class)->execute($batch);

        $this->assertDatabaseHas('activity_logs', [
            'event' => 'sales_import_batch.uploaded',
            'actor_id' => $actor->id,
            'subject_id' => $batch->id,
        ]);
        $this->assertDatabaseHas('activity_logs', [
            'event' => 'sales_import_batch.processed',
            'actor_id' => $actor->id,
            'subject_id' => $batch->id,
        ]);
    }

    public function test_activity_log_page_is_limited_to_privileged_users(): void
    {
        $this->seed(RoleSeeder::class);

        $admin = User::factory()->create(array_merge(
            ['email_verified_at' => now()],
            $this->confirmedTwoFactorAttributes(),
        ));
        $admin->assignRole(RoleEnum::ADMIN->value);

        $sudo = $this->makeSudo(array_merge(
            ['email_verified_at' => now()],
            $this->confirmedTwoFactorAttributes(),
        ));

        $unprivileged = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($admin)->get('/admin/activity-logs')->assertOk();
        $this->actingAs($sudo)->get('/admin/activity-logs')->assertOk();
        $this->actingAs($unprivileged)->get('/admin/activity-logs')->assertForbidden();
    }
}
