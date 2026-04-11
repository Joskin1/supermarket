<?php

namespace Tests\Feature\Sales;

use App\Enums\RoleEnum;
use App\Models\SalesImportBatch;
use App\Models\SalesRecord;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class SalesAdminPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_users_can_visit_the_sales_pages(): void
    {
        $this->seed(RoleSeeder::class);

        $admin = User::factory()->create();
        $admin->assignRole(RoleEnum::ADMIN->value);

        $this->actingAs($admin);

        $this->get('/admin/daily-sales-export')
            ->assertOk()
            ->assertSeeText('Ctrl+Shift+:')
            ->assertSeeText('fixed value');
        $this->get('/admin/sales-import-batches')->assertOk();
        $this->get('/admin/sales-records')->assertOk();
    }

    public function test_sudo_users_can_visit_the_sales_pages(): void
    {
        $this->seed();

        $sudoUser = User::query()
            ->where('email', env('SUDO_EMAIL', 'akinjoseph221@gmail.com'))
            ->firstOrFail();

        $this->actingAs($sudoUser);

        $this->get('/admin/daily-sales-export')->assertOk();
        $this->get('/admin/sales-import-batches')->assertOk();
        $this->get('/admin/sales-records')->assertOk();
    }

    public function test_users_without_sales_access_are_denied_by_policy(): void
    {
        $user = User::factory()->create();

        $this->assertFalse(Gate::forUser($user)->allows('viewAny', SalesImportBatch::class));
        $this->assertFalse(Gate::forUser($user)->allows('viewAny', SalesRecord::class));
    }
}
