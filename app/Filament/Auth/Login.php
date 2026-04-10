<?php

namespace App\Filament\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;

class Login extends BaseLogin
{
    public function mount(): void
    {
        parent::mount();

        if (request()->boolean('verified')) {
            Notification::make()
                ->title('Email verified')
                ->body('Your email address has been verified. You can now sign in to the admin panel.')
                ->success()
                ->send();
        }
    }
}
