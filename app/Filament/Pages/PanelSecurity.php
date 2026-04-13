<?php

namespace App\Filament\Pages;

use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Pages\Dashboard;
use Filament\Pages\Page;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Laravel\Fortify\Actions\GenerateNewRecoveryCodes;
use Laravel\Fortify\Features;
use Laravel\Fortify\Fortify;
use Throwable;

class PanelSecurity extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = null;

    protected static ?string $navigationLabel = null;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug = 'security';

    protected string $view = 'filament.pages.panel-security';

    public bool $canManageTwoFactor = false;

    public bool $twoFactorEnabled = false;

    public bool $requiresConfirmation = false;

    public bool $showEnforcementNotice = false;

    public bool $showSetup = false;

    public string $qrCodeSvg = '';

    public string $manualSetupKey = '';

    public string $code = '';

    /**
     * @var array<int, string>
     */
    public array $recoveryCodes = [];

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user instanceof User && ($user->isAdmin() || $user->isSudo());
    }

    public function getHeading(): string
    {
        return 'Panel Security';
    }

    public function getSubheading(): string
    {
        return 'Manage two-factor authentication for privileged admin-panel access.';
    }

    public function mount(DisableTwoFactorAuthentication $disableTwoFactorAuthentication): void
    {
        $this->showEnforcementNotice = request()->boolean('enforce2fa');
        $this->canManageTwoFactor = Features::canManageTwoFactorAuthentication();
        $this->requiresConfirmation = $this->canManageTwoFactor
            && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirm');

        if (
            $this->canManageTwoFactor
            && Fortify::confirmsTwoFactorAuthentication()
            && auth()->user()?->two_factor_secret
            && is_null(auth()->user()?->two_factor_confirmed_at)
        ) {
            $disableTwoFactorAuthentication(auth()->user());
        }

        $this->syncTwoFactorState();
    }

    public function startTwoFactorSetup(EnableTwoFactorAuthentication $enableTwoFactorAuthentication): void
    {
        if (! $this->canManageTwoFactor || $this->twoFactorEnabled) {
            return;
        }

        $enableTwoFactorAuthentication(auth()->user());

        $user = auth()->user()?->fresh();

        if (! $user?->two_factor_secret) {
            Notification::make()
                ->danger()
                ->title('Two-factor setup failed')
                ->body('The setup secret could not be created. Please try again.')
                ->send();

            return;
        }

        $this->qrCodeSvg = $user->twoFactorQrCodeSvg();
        $this->manualSetupKey = decrypt($user->two_factor_secret);
        $this->showSetup = true;
        $this->code = '';
        $this->resetErrorBag();
    }

    public function confirmTwoFactor(ConfirmTwoFactorAuthentication $confirmTwoFactorAuthentication): void
    {
        $this->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        try {
            $confirmTwoFactorAuthentication(auth()->user(), $this->code);
        } catch (Throwable) {
            throw ValidationException::withMessages([
                'code' => 'The authentication code is invalid. Enter the latest 6-digit code from your authenticator app.',
            ]);
        }

        $this->showSetup = false;
        $this->qrCodeSvg = '';
        $this->manualSetupKey = '';
        $this->code = '';

        $this->syncTwoFactorState();

        Notification::make()
            ->success()
            ->title('Two-factor authentication enabled')
            ->body('You can now continue to the admin panel.')
            ->send();

        if ($this->showEnforcementNotice) {
            $this->redirect(Dashboard::getUrl(panel: 'admin', isAbsolute: false), navigate: true);
        }
    }

    public function disableTwoFactor(DisableTwoFactorAuthentication $disableTwoFactorAuthentication): void
    {
        $disableTwoFactorAuthentication(auth()->user());

        $this->showSetup = false;
        $this->qrCodeSvg = '';
        $this->manualSetupKey = '';
        $this->code = '';

        $this->syncTwoFactorState();

        Notification::make()
            ->success()
            ->title('Two-factor authentication disabled')
            ->send();
    }

    public function regenerateRecoveryCodes(GenerateNewRecoveryCodes $generateNewRecoveryCodes): void
    {
        $generateNewRecoveryCodes(auth()->user());

        $this->loadRecoveryCodes();

        Notification::make()
            ->success()
            ->title('Recovery codes regenerated')
            ->send();
    }

    protected function syncTwoFactorState(): void
    {
        $user = auth()->user()?->fresh();

        $this->twoFactorEnabled = (bool) $user?->hasConfirmedTwoFactorAuthentication();
        $this->loadRecoveryCodes();
    }

    protected function loadRecoveryCodes(): void
    {
        $user = auth()->user()?->fresh();

        if (! $user?->hasEnabledTwoFactorAuthentication() || blank($user->two_factor_recovery_codes)) {
            $this->recoveryCodes = [];

            return;
        }

        try {
            $this->recoveryCodes = json_decode(decrypt($user->two_factor_recovery_codes), true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            $this->recoveryCodes = [];

            Notification::make()
                ->danger()
                ->title('Recovery codes unavailable')
                ->body('The saved recovery codes could not be read. Regenerate them to continue.')
                ->send();
        }
    }
}
