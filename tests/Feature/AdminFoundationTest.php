<?php

namespace Tests\Feature;

use App\Enums\RoleEnum;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class AdminFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_bootstraps_the_sudo_user_with_the_sudo_role(): void
    {
        $this->seed();

        $sudoUser = User::query()
            ->where('email', 'akinjoseph221@gmail.com')
            ->first();

        $this->assertNotNull($sudoUser);
        $this->assertTrue($sudoUser->isSudo());
        $this->assertNotNull($sudoUser->email_verified_at);
    }

    public function test_admin_users_can_access_the_filament_panel(): void
    {
        $this->seed(RoleSeeder::class);

        $admin = User::factory()->create();
        $admin->assignRole(RoleEnum::ADMIN->value);

        $this->assertTrue($admin->canAccessPanel(Filament::getPanel('admin')));
    }

    public function test_users_without_roles_cannot_access_the_filament_panel(): void
    {
        $user = User::factory()->create();

        $this->assertFalse($user->canAccessPanel(Filament::getPanel('admin')));
    }

    public function test_panel_password_reset_screen_can_be_rendered(): void
    {
        $this->get('/admin/password-reset/request')
            ->assertOk();
    }

    public function test_only_sudo_users_can_manage_users(): void
    {
        $this->seed();

        $admin = User::factory()->create();
        $admin->assignRole(RoleEnum::ADMIN->value);

        $sudoUser = User::query()
            ->where('email', 'akinjoseph221@gmail.com')
            ->firstOrFail();

        $this->assertFalse(Gate::forUser($admin)->allows('viewAny', User::class));
        $this->assertTrue(Gate::forUser($sudoUser)->allows('viewAny', User::class));
    }
}
