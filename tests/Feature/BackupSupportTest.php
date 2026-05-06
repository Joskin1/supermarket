<?php

namespace Tests\Feature;

use App\Actions\Maintenance\CreateBackupSnapshotAction;
use App\Actions\Maintenance\RestoreBackupSnapshotAction;
use App\Enums\RoleEnum;
use App\Models\ActivityLog;
use App\Models\BackupRun;
use App\Models\Category;
use App\Models\Product;
use App\Models\SalesImportBatch;
use App\Models\SalesRecord;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BackupSupportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
    }

    public function test_backup_snapshot_action_creates_a_private_recovery_file_and_metadata(): void
    {
        $creator = User::factory()->create();
        $category = Category::factory()->create(['name' => 'Groceries']);
        Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Rice',
            'sku' => 'SKU-BACKUP-1001',
        ]);

        $backupRun = app(CreateBackupSnapshotAction::class)->execute(
            createdBy: $creator->id,
            note: 'Before weekend close',
        );

        Storage::disk('local')->assertExists($backupRun->file_path);

        $this->assertSame('completed', $backupRun->status);
        $this->assertNotNull($backupRun->checksum);
        $this->assertNotNull($backupRun->file_size_bytes);

        $payload = json_decode(Storage::disk('local')->get($backupRun->file_path), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame('business_data', $payload['metadata']['snapshot_type']);
        $this->assertSame($backupRun->backup_code, $payload['metadata']['backup_code']);
        $this->assertContains('products', $payload['metadata']['tables']);
        $this->assertContains('users', $payload['metadata']['excluded_areas']);
        $this->assertNotEmpty($payload['tables']['products']);

        $this->assertDatabaseHas('activity_logs', [
            'event' => 'backup.created',
            'actor_id' => $creator->id,
            'subject_id' => $backupRun->id,
        ]);
    }

    public function test_backup_command_creates_a_completed_backup_run(): void
    {
        $this->artisan('backups:create', ['--note' => 'Manual test backup'])
            ->assertSuccessful()
            ->expectsOutputToContain('Backup created:');

        $this->assertDatabaseHas('backup_runs', [
            'status' => 'completed',
        ]);
    }

    public function test_only_sudo_users_can_access_backup_pages(): void
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

        $this->actingAs($sudo)->get('/admin/backup-runs')->assertOk();
        $this->actingAs($admin)->get('/admin/backup-runs')->assertForbidden();
    }

    public function test_restore_replaces_business_data_from_the_snapshot_scope(): void
    {
        $creator = User::factory()->create();
        $category = Category::factory()->create(['name' => 'Groceries']);
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Rice',
            'sku' => 'SKU-BACKUP-RESTORE-1001',
            'current_stock' => 18,
        ]);

        $backupRun = app(CreateBackupSnapshotAction::class)->execute(
            createdBy: $creator->id,
            note: 'Before stock changes',
        );

        $category->update(['name' => 'Changed Category']);
        $product->update([
            'name' => 'Changed Product',
            'current_stock' => 4,
        ]);

        app(RestoreBackupSnapshotAction::class)->execute(
            backupRun: $backupRun,
            restoredBy: $creator->id,
            note: 'Rollback to snapshot',
        );

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Groceries',
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Rice',
            'current_stock' => 18,
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'event' => 'backup.restored',
            'actor_id' => $creator->id,
            'subject_id' => $backupRun->id,
        ]);

        $this->assertGreaterThanOrEqual(2, BackupRun::query()->count());
    }

    public function test_restore_rolls_back_when_an_error_occurs_mid_restore(): void
    {
        $creator = User::factory()->create();
        $category = Category::factory()->create(['name' => 'Groceries']);
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Rice',
            'sku' => 'SKU-BACKUP-ROLLBACK-1001',
            'current_stock' => 18,
        ]);

        $backupRun = app(CreateBackupSnapshotAction::class)->execute(
            createdBy: $creator->id,
            note: 'Before stock changes',
        );

        $category->update(['name' => 'Changed Category']);
        $product->update([
            'name' => 'Changed Product',
            'current_stock' => 4,
        ]);

        $action = new class(app(CreateBackupSnapshotAction::class)) extends RestoreBackupSnapshotAction
        {
            protected function restoreTable(string $table, array $rows): void
            {
                parent::restoreTable($table, $rows);

                if ($table === 'products') {
                    throw new \RuntimeException('Simulated restore failure.');
                }
            }
        };

        try {
            $action->execute(
                backupRun: $backupRun,
                restoredBy: $creator->id,
                note: 'Expect rollback',
            );

            $this->fail('The restore should have failed.');
        } catch (\RuntimeException $exception) {
            $this->assertSame('Simulated restore failure.', $exception->getMessage());
        }

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Changed Category',
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Changed Product',
            'current_stock' => 4,
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'event' => 'backup.restore_failed',
            'actor_id' => $creator->id,
            'subject_id' => $backupRun->id,
        ]);
    }

    public function test_restore_nulls_missing_user_references_from_business_snapshot_rows(): void
    {
        $creator = User::factory()->create();
        $restorer = User::factory()->create();
        $category = Category::factory()->create(['name' => 'Groceries']);
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Rice',
            'sku' => 'SKU-BACKUP-USERREF-1001',
        ]);

        $batch = SalesImportBatch::factory()->processed()->create([
            'uploaded_by' => $creator->id,
            'sales_date_from' => '2026-04-10',
            'sales_date_to' => '2026-04-10',
        ]);

        $salesRecord = SalesRecord::factory()->forProduct($product)->create([
            'batch_id' => $batch->id,
            'created_by' => $creator->id,
            'sales_date' => '2026-04-10',
        ]);

        $activityLog = ActivityLog::factory()->create([
            'actor_id' => $creator->id,
            'event' => 'restore.user-reference-check',
            'description' => 'Snapshot user reference check.',
        ]);

        $backupRun = app(CreateBackupSnapshotAction::class)->execute(
            createdBy: $creator->id,
            note: 'Before deleting source user',
        );

        $creator->forceDelete();

        app(RestoreBackupSnapshotAction::class)->execute(
            backupRun: $backupRun,
            restoredBy: $restorer->id,
            note: 'Restore after removing source user',
        );

        $this->assertDatabaseHas('sales_import_batches', [
            'id' => $batch->id,
            'uploaded_by' => null,
        ]);

        $this->assertDatabaseHas('sales_records', [
            'id' => $salesRecord->id,
            'created_by' => null,
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'id' => $activityLog->id,
            'actor_id' => null,
            'event' => 'restore.user-reference-check',
        ]);
    }
}
