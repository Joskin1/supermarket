<?php

namespace Tests\Feature;

use App\Enums\RoleEnum;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_only_bootstraps_roles_by_default(): void
    {
        $this->seed();

        $this->assertDatabaseCount('users', 0);
        $this->assertNotNull(Role::findByName(RoleEnum::SUDO->value, 'web'));
        $this->assertNotNull(Role::findByName(RoleEnum::ADMIN->value, 'web'));
    }

    public function test_bootstrap_sudo_command_creates_a_verified_sudo_user(): void
    {
        $this->seed(RoleSeeder::class);

        $this->artisan('users:bootstrap-sudo', [
            'email' => 'owner@example.com',
            '--name' => 'Store Owner',
            '--password' => 'StrongPassword!123',
        ])->assertSuccessful();

        $sudoUser = User::query()
            ->where('email', 'owner@example.com')
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
        $this->seed(RoleSeeder::class);

        $admin = User::factory()->create();
        $admin->assignRole(RoleEnum::ADMIN->value);

        $sudoUser = $this->makeSudo();

        $this->assertFalse(Gate::forUser($admin)->allows('viewAny', User::class));
        $this->assertTrue(Gate::forUser($sudoUser)->allows('viewAny', User::class));
    }
}
