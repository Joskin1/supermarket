<?php

namespace Tests\Feature;

use App\Actions\Maintenance\CreateBackupSnapshotAction;
use App\Enums\RoleEnum;
use App\Models\Category;
use App\Models\Product;
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

        $this->assertSame($backupRun->backup_code, $payload['metadata']['backup_code']);
        $this->assertContains('products', $payload['metadata']['tables']);
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
}
