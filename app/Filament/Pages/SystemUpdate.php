<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Cache;
use Native\Desktop\Facades\AutoUpdater;

class SystemUpdate extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-arrow-path';
    protected static \UnitEnum|string|null $navigationGroup = 'System';
    protected static ?int $navigationSort = 110;
    protected string $view = 'filament.pages.system-update';

    public string $currentVersion;
    public bool $isDesktop;

    public function mount(): void
    {
        $this->isDesktop = (bool) env('NATIVEPHP_RUNNING', false);
        $this->currentVersion = config('nativephp.version', '1.0.0');
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user && ($user->isSudo() || $user->isAdmin());
    }

    public function getUpdateStatusProperty(): string
    {
        return Cache::get('update_download_status', 'idle');
    }

    public function getLatestVersionProperty(): ?string
    {
        return Cache::get('latest_version_available');
    }

    public function getDownloadProgressProperty(): int
    {
        return Cache::get('update_download_progress', 0);
    }

    public function checkUpdates(): void
    {
        if (! $this->isDesktop) {
            return;
        }

        try {
            Notification::make()
                ->title('Checking for updates...')
                ->info()
                ->send();
                
            AutoUpdater::checkForUpdates();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Update Check Failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function startDownload(): void
    {
        if (! $this->isDesktop) {
            return;
        }

        try {
            Cache::put('update_download_status', 'downloading');
            Cache::put('update_download_progress', 0);

            AutoUpdater::downloadUpdate();

            Notification::make()
                ->title('Download Started')
                ->body('The update is downloading in the background. You can monitor progress here or in the sidebar.')
                ->success()
                ->send();
        } catch (\Throwable $e) {
            Cache::put('update_download_status', 'available');
            
            Notification::make()
                ->title('Download Failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function installUpdate(): void
    {
        if (! $this->isDesktop) {
            return;
        }

        try {
            AutoUpdater::quitAndInstall();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Installation Failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function resetUpdateState(): void
    {
        Cache::put('update_download_status', 'idle');
        Cache::forget('latest_version_available');
        Cache::forget('update_download_progress');

        Notification::make()
            ->title('Update state reset completed.')
            ->info()
            ->send();
    }
}
