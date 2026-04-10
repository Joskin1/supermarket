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

        $admin = User::factory()->create();
        $admin->assignRole(RoleEnum::ADMIN->value);

        $this->actingAs($admin);

        $this->get('/admin/categories')->assertOk();
        $this->get('/admin/products')->assertOk();
        $this->get('/admin/stock-entries')->assertOk();
    }

    public function test_sudo_users_can_visit_the_inventory_resource_pages(): void
    {
        $this->seed();

        $sudoUser = User::query()
            ->where('email', env('SUDO_EMAIL', 'akinjoseph221@gmail.com'))
            ->firstOrFail();

        $this->actingAs($sudoUser);

        $this->get('/admin/categories')->assertOk();
        $this->get('/admin/products')->assertOk();
        $this->get('/admin/stock-entries')->assertOk();
    }
}
