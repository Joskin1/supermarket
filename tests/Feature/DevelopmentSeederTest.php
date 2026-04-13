<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\BackupRun;
use App\Models\Category;
use App\Models\DailySalesSummary;
use App\Models\Product;
use App\Models\SalesImportBatch;
use App\Models\SalesRecord;
use App\Models\StockAdjustment;
use App\Models\StockEntry;
use App\Models\SystemSetting;
use App\Models\User;
use Database\Seeders\ApplicationDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DevelopmentSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_application_demo_seeder_prepares_a_full_local_dataset(): void
    {
        Storage::fake('local');

        $this->seed(ApplicationDemoSeeder::class);

        $sudo = User::query()->where('email', 'akinjoseph221@gmail.com')->first();

        $this->assertNotNull($sudo);
        $this->assertTrue($sudo->isSudo());
        $this->assertDatabaseHas('users', [
            'email' => 'store-manager@supermarket.test',
        ]);
        $this->assertGreaterThan(0, Category::query()->count());
        $this->assertGreaterThan(0, Product::query()->count());
        $this->assertGreaterThan(0, StockEntry::query()->count());
        $this->assertGreaterThan(0, StockAdjustment::query()->count());
        $this->assertGreaterThan(0, SalesImportBatch::query()->count());
        $this->assertGreaterThan(0, SalesRecord::query()->count());
        $this->assertGreaterThan(0, DailySalesSummary::query()->count());
        $this->assertSame(1, SystemSetting::query()->count());
        $this->assertDatabaseHas('activity_logs', [
            'event' => 'backup.created',
        ]);

        $backupRun = BackupRun::query()->first();

        $this->assertNotNull($backupRun);
        Storage::disk('local')->assertExists($backupRun->file_path);
        $this->assertGreaterThan(0, ActivityLog::query()->count());
    }
}
