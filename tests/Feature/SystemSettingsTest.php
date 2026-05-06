<?php

namespace Tests\Feature;

use App\Enums\RoleEnum;
use App\Models\SystemSetting;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SystemSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_system_settings_bootstrap_with_application_defaults(): void
    {
        $settings = SystemSetting::current();

        $this->assertSame(config('app.name'), $settings->business_name);
        $this->assertSame(config('app.timezone'), $settings->business_timezone);
        $this->assertSame('NGN', $settings->currency_code);
    }

    public function test_system_settings_current_returns_the_same_singleton_record(): void
    {
        $first = SystemSetting::current();
        $second = SystemSetting::current();

        $this->assertSame($first->id, $second->id);
        $this->assertSame(SystemSetting::SINGLETON_KEY, $first->singleton_key);
        $this->assertDatabaseCount('system_settings', 1);
    }

    public function test_only_sudo_users_can_access_system_settings_pages(): void
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

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $setting = SystemSetting::current();

        $this->actingAs($sudo)->get('/admin/system-settings')->assertOk();
        $this->actingAs($sudo)->get('/admin/system-settings/'.$setting->id.'/edit')->assertOk();

        $this->actingAs($admin)->get('/admin/system-settings')->assertForbidden();
        $this->actingAs($user)->get('/admin/system-settings')->assertForbidden();
    }
}
