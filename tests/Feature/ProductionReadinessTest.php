<?php

namespace Tests\Feature;

use App\Models\SystemSetting;
use App\Support\Operations\ProductionReadinessCheck;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductionReadinessTest extends TestCase
{
    use RefreshDatabase;

    public function test_production_readiness_check_reports_high_risk_launch_issues(): void
    {
        config([
            'app.env' => 'production',
            'app.debug' => true,
            'app.url' => 'http://supermarket.example',
            'mail.default' => 'log',
            'queue.default' => 'sync',
        ]);

        SystemSetting::factory()->create();
        SystemSetting::factory()->legacy()->create();

        $issues = app(ProductionReadinessCheck::class)->issues();

        $titles = collect($issues)->pluck('title')->all();

        $this->assertContains('APP_DEBUG is enabled', $titles);
        $this->assertContains('APP_URL is not HTTPS', $titles);
        $this->assertContains('Mail is not deliverable', $titles);
        $this->assertContains('Queue is running in sync mode', $titles);
        $this->assertContains('Legacy settings rows detected', $titles);
    }

    public function test_production_readiness_check_stays_quiet_for_clean_local_or_ready_configs(): void
    {
        config([
            'app.env' => 'production',
            'app.debug' => false,
            'app.url' => 'https://supermarket.example',
            'mail.default' => 'smtp',
            'queue.default' => 'database',
        ]);

        SystemSetting::current();

        $this->assertSame([], app(ProductionReadinessCheck::class)->issues());
    }
}
