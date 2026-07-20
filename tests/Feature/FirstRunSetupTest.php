<?php

namespace Tests\Feature;

use App\Enums\RoleEnum;
use App\Filament\Auth\Login;
use App\Livewire\FirstRunSetupPage;
use App\Models\SystemSetting;
use App\Models\User;
use App\Support\FirstRunSetup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class FirstRunSetupTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_install_is_sent_to_first_run_setup(): void
    {
        $this->get(route('home'))
            ->assertRedirect(route('setup'));
    }

    public function test_first_run_setup_creates_local_sudo_user_and_business_profile(): void
    {
        Livewire::test(FirstRunSetupPage::class)
            ->set('businessName', 'White Mart')
            ->set('businessType', 'Supermarket')
            ->set('businessTimezone', 'Africa/Lagos')
            ->set('currencyCode', 'ngn')
            ->set('businessPhone', '08012345678')
            ->set('businessEmail', 'store@example.test')
            ->set('businessAddress', '12 Market Road')
            ->set('ownerName', 'Store Owner')
            ->set('password', 'StrongPassword123')
            ->set('passwordConfirmation', 'StrongPassword123')
            ->call('save')
            ->assertRedirect('/admin');

        $owner = User::query()->where('email', FirstRunSetup::LOCAL_OWNER_EMAIL)->first();
        $settings = SystemSetting::current();

        $this->assertNotNull($owner);
        $this->assertTrue($owner->hasRole(RoleEnum::SUDO->value));
        $this->assertTrue(Hash::check('StrongPassword123', $owner->password));
        $this->assertSame('White Mart', $settings->business_name);
        $this->assertSame('NGN', $settings->currency_code);
        $this->assertNotNull($settings->business_profile_completed_at);
        $this->assertAuthenticatedAs($owner);
    }

    public function test_filament_login_uses_password_only_after_setup(): void
    {
        $this->makeSudo();

        Livewire::test(Login::class)
            ->assertFormFieldDoesNotExist('email')
            ->assertFormFieldExists('password');
    }
}
