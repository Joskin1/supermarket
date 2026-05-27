<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Native\Desktop\Facades\AutoUpdater;
use Native\Desktop\Events\AutoUpdater\UpdateAvailable;
use Native\Desktop\Events\AutoUpdater\UpdateNotAvailable;
use Native\Desktop\Events\AutoUpdater\UpdateDownloaded;
use Native\Desktop\Events\AutoUpdater\Error as UpdaterError;
use Illuminate\Support\Facades\Event;

class SystemUpdate extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';
    protected static ?string $navigationGroup = 'System';
    protected static ?int $navigationSort = 110;
    protected static string $view = 'filament.pages.system-update';

    public string $currentVersion;
    public bool $isDesktop;

    public function mount()
    {
        $this->isDesktop = (bool) env('NATIVEPHP_RUNNING', false);
        $this->currentVersion = config('nativephp.version', '1.0.0');

        $this->registerUpdateListeners();
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user && ($user->isSudo() || $user->isAdmin());
    }

    protected function registerUpdateListeners()
    {
        if (! $this->isDesktop) return;

        Event::listen(UpdateAvailable::class, function () {
            Notification::make()
                ->title('Update Available!')
                ->body('A new version is available. Downloading in the background...')
                ->info()
                ->send();
        });

        Event::listen(UpdateNotAvailable::class, function () {
            Notification::make()
                ->title('Up to Date')
                ->body('You are running the latest version.')
                ->success()
                ->send();
        });

        Event::listen(UpdaterError::class, function ($event) {
            Notification::make()
                ->title('Update Failed')
                ->body('An error occurred: ' . ($event->message ?? 'Unknown error'))
                ->danger()
                ->send();
        });

        Event::listen(UpdateDownloaded::class, function () {
            Notification::make()
                ->title('Update Ready')
                ->body('The update has been downloaded and is ready to install.')
                ->success()
                ->actions([
                    \Filament\Notifications\Actions\Action::make('install')
                        ->label('Install & Restart')
                        ->button()
                        ->action(function () {
                            AutoUpdater::quitAndInstall();
                        })
                ])
                ->persistent()
                ->send();
        });
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('check_updates')
                ->label('Check for Updates')
                ->icon('heroicon-o-cloud-arrow-down')
                ->color('primary')
                ->visible(fn () => $this->isDesktop)
                ->action(function () {
                    try {
                        Notification::make()
                            ->title('Checking for updates...')
                            ->info()
                            ->send();
                            
                        AutoUpdater::checkForUpdates();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Update Check Failed')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
                
            Action::make('restart_app')
                ->label('Restart Application')
                ->icon('heroicon-o-power')
                ->color('warning')
                ->visible(fn () => $this->isDesktop)
                ->requiresConfirmation()
                ->action(function () {
                    AutoUpdater::quitAndInstall();
                }),
        ];
    }
}
