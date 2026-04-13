<?php

namespace Tests\Feature\Reporting;

use App\Enums\RoleEnum;
use App\Filament\Widgets\Reports\CategorySalesDistributionChart;
use App\Filament\Widgets\Reports\SalesAmountTrendChart;
use App\Filament\Widgets\Reports\SalesQuantityTrendChart;
use App\Filament\Widgets\Reports\TopProductsChart;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportPageAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_users_can_access_all_reporting_pages(): void
    {
        $this->seed(RoleSeeder::class);

        $admin = User::factory()->create(array_merge(
            ['email_verified_at' => now()],
            $this->confirmedTwoFactorAttributes(),
        ));
        $admin->assignRole(RoleEnum::ADMIN->value);

        $this->actingAs($admin);

        foreach ($this->reportRoutes() as $route) {
            $this->get($route)->assertOk();
        }
    }

    public function test_sudo_users_can_access_all_reporting_pages(): void
    {
        $sudo = $this->makeSudo(array_merge(
            ['email_verified_at' => now()],
            $this->confirmedTwoFactorAttributes(),
        ));

        $this->actingAs($sudo);

        foreach ($this->reportRoutes() as $route) {
            $this->get($route)->assertOk();
        }
    }

    public function test_sudo_users_can_view_reporting_charts(): void
    {
        $sudo = $this->makeSudo([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($sudo);

        $this->assertTrue(SalesAmountTrendChart::canView());
        $this->assertTrue(SalesQuantityTrendChart::canView());
        $this->assertTrue(CategorySalesDistributionChart::canView());
        $this->assertTrue(TopProductsChart::canView());
    }

    public function test_users_without_roles_cannot_access_reporting_pages(): void
    {
        $this->actingAs(User::factory()->create());

        foreach ($this->reportRoutes() as $route) {
            $this->get($route)->assertForbidden();
        }
    }

    /**
     * @return array<int, string>
     */
    protected function reportRoutes(): array
    {
        return [
            '/admin/reports/daily-report',
            '/admin/reports/weekly-summary',
            '/admin/reports/sales-trends',
            '/admin/reports/low-stock-report',
            '/admin/reports/top-performance',
        ];
    }
}
