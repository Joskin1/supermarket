<?php

namespace Tests\Feature\Inventory;

use App\Enums\RoleEnum;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryAdminPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_users_can_visit_the_inventory_resource_pages(): void
    {
        $this->seed(RoleSeeder::class);

        $admin = User::factory()->create(array_merge(
            ['email_verified_at' => now()],
            $this->confirmedTwoFactorAttributes(),
        ));
        $admin->assignRole(RoleEnum::ADMIN->value);

        $this->actingAs($admin);

        $this->get('/admin/categories')->assertOk();
        $this->get('/admin/products')->assertOk();
        $this->get('/admin/stock-entries')->assertOk();
        $this->get('/admin/stock-adjustments')->assertOk();
    }

    public function test_sudo_users_can_visit_the_inventory_resource_pages(): void
    {
        $sudoUser = $this->makeSudo(array_merge(
            ['email_verified_at' => now()],
            $this->confirmedTwoFactorAttributes(),
        ));

        $this->actingAs($sudoUser);

        $this->get('/admin/categories')->assertOk();
        $this->get('/admin/products')->assertOk();
        $this->get('/admin/stock-entries')->assertOk();
        $this->get('/admin/stock-adjustments')->assertOk();
    }
}
