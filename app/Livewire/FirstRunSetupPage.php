<?php

namespace App\Livewire;

use App\Enums\RoleEnum;
use App\Models\SystemSetting;
use App\Models\User;
use App\Support\FirstRunSetup;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class FirstRunSetupPage extends Component
{
    public string $businessName = '';

    public string $businessType = '';

    public string $businessTimezone = 'Africa/Lagos';

    public string $currencyCode = 'NGN';

    public string $businessPhone = '';

    public string $businessEmail = '';

    public string $businessAddress = '';

    public string $ownerName = '';

    public string $password = '';

    public string $passwordConfirmation = '';

    public function mount(): void
    {
        if (FirstRunSetup::isComplete()) {
            $this->redirect('/admin', navigate: true);

            return;
        }

        $settings = SystemSetting::current();

        $this->businessName = $settings->business_name ?: '';
        $this->businessType = $settings->business_type ?: '';
        $this->businessTimezone = $settings->business_timezone ?: config('app.timezone', 'Africa/Lagos');
        $this->currencyCode = $settings->currency_code ?: 'NGN';
        $this->businessPhone = $settings->business_phone ?: '';
        $this->businessEmail = $settings->business_email ?: '';
        $this->businessAddress = $settings->business_address ?: '';
    }

    public function save(): void
    {
        $data = $this->validate([
            'businessName' => ['required', 'string', 'max:255'],
            'businessType' => ['nullable', 'string', 'max:255'],
            'businessTimezone' => ['required', 'string', Rule::in(timezone_identifiers_list())],
            'currencyCode' => ['required', 'string', 'size:3'],
            'businessPhone' => ['nullable', 'string', 'max:255'],
            'businessEmail' => ['nullable', 'email', 'max:255'],
            'businessAddress' => ['nullable', 'string', 'max:500'],
            'ownerName' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Role::findOrCreate(RoleEnum::SUDO->value, 'web');
        Role::findOrCreate(RoleEnum::ADMIN->value, 'web');

        $settings = SystemSetting::current();
        $settings->forceFill([
            'business_name' => $data['businessName'],
            'business_type' => $data['businessType'] ?: null,
            'business_timezone' => $data['businessTimezone'],
            'currency_code' => Str::upper($data['currencyCode']),
            'business_phone' => $data['businessPhone'] ?: null,
            'business_email' => $data['businessEmail'] ?: null,
            'business_address' => $data['businessAddress'] ?: null,
        ])->save();
        $settings->markBusinessProfileComplete();

        $owner = User::query()->firstOrNew(['email' => FirstRunSetup::LOCAL_OWNER_EMAIL]);
        $owner->forceFill([
            'name' => $data['ownerName'],
            'password' => Hash::make($data['password']),
            'email_verified_at' => now(),
        ])->save();
        $owner->syncRoles([RoleEnum::SUDO->value]);

        Auth::login($owner);
        session()->regenerate();

        $this->redirect('/admin', navigate: true);
    }

    public function render()
    {
        return view('livewire.first-run-setup-page')
            ->layout('layouts.auth', ['title' => 'Set up']);
    }
}
