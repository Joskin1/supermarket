<?php

namespace App\Filament\Auth;

use App\Support\FirstRunSetup;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Models\Contracts\FilamentUser;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Schema;
use Illuminate\Auth\Events\Failed;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class Login extends BaseLogin
{
    public function mount(): void
    {
        if (FirstRunSetup::needsSetup()) {
            redirect(FirstRunSetup::setupUrl());

            return;
        }

        parent::mount();

        if (request()->boolean('verified')) {
            Notification::make()
                ->title('Email verified')
                ->body('Your email address has been verified. You can now sign in to the admin panel.')
                ->success()
                ->send();
        }
    }

    public function authenticate(): ?LoginResponse
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return null;
        }

        $data = $this->form->getState();
        $user = $this->findLocalUserForPassword((string) $data['password']);

        if (! $user) {
            event(app(Failed::class, [
                'guard' => 'web',
                'user' => null,
                'credentials' => ['password' => '[password-only]'],
            ]));

            $this->throwFailureValidationException();
        }

        if (
            $user instanceof FilamentUser
            && ! $user->canAccessPanel(Filament::getCurrentOrDefaultPanel())
        ) {
            $this->throwFailureValidationException();
        }

        Filament::auth()->login($user, (bool) ($data['remember'] ?? false));
        session()->regenerate();

        return app(LoginResponse::class);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getPasswordFormComponent(),
                $this->getRememberFormComponent(),
            ]);
    }

    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label('Password')
            ->password()
            ->revealable(filament()->arePasswordsRevealable())
            ->autocomplete('current-password')
            ->autofocus()
            ->required();
    }

    protected function throwFailureValidationException(): never
    {
        throw ValidationException::withMessages([
            'data.password' => 'The password is incorrect.',
        ]);
    }

    protected function findLocalUserForPassword(string $password): ?Authenticatable
    {
        $authProvider = Filament::auth()->getProvider();

        $users = $authProvider->getModel()::query()
            ->whereHas('roles', fn ($query) => $query->whereIn('name', ['sudo', 'admin']))
            ->oldest('id')
            ->get();

        foreach ($users as $user) {
            if (Hash::check($password, $user->password)) {
                if (Hash::needsRehash($user->password)) {
                    $user->forceFill([
                        'password' => Hash::make($password),
                    ])->save();
                }

                return $user;
            }
        }

        return null;
    }
}
