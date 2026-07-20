<?php

namespace App\Providers;

use App\Events\Desktop\BackupShortcutPressed;
use App\Events\Desktop\InventoryShortcutPressed;
use App\Events\Desktop\NewSaleShortcutPressed;
use App\Services\Desktop\BackupService;
use App\Support\BusinessProfile;
use Illuminate\Support\Facades\Event;
use Native\Desktop\Contracts\ProvidesPhpIni;
use Native\Desktop\Facades\GlobalShortcut;
use Native\Desktop\Facades\Menu;
use Native\Desktop\Facades\Window;
use Native\Desktop\Windows\Window as NativeWindow;

class NativeAppServiceProvider implements ProvidesPhpIni
{
    /**
     * Executed once the native application has been booted.
     * Use this method to open windows, register global shortcuts, etc.
     */
    public function boot(): void
    {
        $this->ensureStorageLink();
        $this->createMainWindow();
        $this->createApplicationMenu();
        $this->registerGlobalShortcuts();
        $this->registerShortcutListeners();
        $this->bootstrapLocalDatabase();
        $this->cacheApplication();
        $this->registerUpdateListeners();
        $this->checkForUpdatesOnStartup();
    }

    /**
     * Ensure the public/storage → storage/app/public symlink exists.
     *
     * NativePHP desktop apps do not automatically create this symlink,
     * but the local disk's 'serve' route and any public file serving
     * depends on it. This is safe to call on every boot — it no-ops
     * when the link already exists.
     */
    protected function ensureStorageLink(): void
    {
        try {
            $link = public_path('storage');
            $target = storage_path('app/public');

            if (! is_dir($target)) {
                @mkdir($target, 0755, true);
            }

            if (! file_exists($link)) {
                // On Windows, PHP's symlink() may fail without admin privileges.
                // Fall back to a directory junction which works for unprivileged users.
                if (PHP_OS_FAMILY === 'Windows') {
                    exec('mklink /J "' . $link . '" "' . $target . '"');
                } else {
                    symlink($target, $link);
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Storage link creation failed: ' . $e->getMessage());
        }
    }

    protected function cacheApplication(): void
    {
        try {
            // Clear any old static configuration, event, or route caches to prevent stale path lockups
            // and database connection blocks on the local desktop machine.
            \Illuminate\Support\Facades\Artisan::call('config:clear');
            \Illuminate\Support\Facades\Artisan::call('route:clear');
            \Illuminate\Support\Facades\Artisan::call('event:clear');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Application Cache Clear Error: ' . $e->getMessage());
        }
    }

    protected function bootstrapLocalDatabase(): void
    {
        try {
            if (! \Illuminate\Support\Facades\Schema::hasTable('users')) {
                \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
            }

            if (\Illuminate\Support\Facades\Schema::hasTable('roles')) {
                \Illuminate\Support\Facades\Artisan::call('db:seed', [
                    '--class' => \Database\Seeders\RoleSeeder::class,
                    '--force' => true,
                ]);
            }

            if (\Illuminate\Support\Facades\Schema::hasTable('system_settings')) {
                \App\Models\SystemSetting::current();
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Database Bootstrap Error: ' . $e->getMessage());
        }
    }

    protected function registerGlobalShortcuts(): void
    {
        GlobalShortcut::key('CmdOrCtrl+Shift+B')->event(BackupShortcutPressed::class)->register();
        GlobalShortcut::key('CmdOrCtrl+Shift+S')->event(NewSaleShortcutPressed::class)->register();
        GlobalShortcut::key('CmdOrCtrl+Shift+I')->event(InventoryShortcutPressed::class)->register();
    }

    protected function registerShortcutListeners(): void
    {
        Event::listen(BackupShortcutPressed::class, function () {
            app(BackupService::class)->createBackup();
        });

        Event::listen(NewSaleShortcutPressed::class, function () {
            (new NativeWindow('main'))->url(url('/admin/sales-records/create'));
        });

        Event::listen(InventoryShortcutPressed::class, function () {
            (new NativeWindow('main'))->url(url('/admin/products'));
        });
    }

    public function phpIni(): array
    {
        return [
            'memory_limit' => '512M',
            'max_execution_time' => '600',
            // Snappy OPcache precompilation for lightning-fast server responses on Windows
            'opcache.enable' => '1',
            'opcache.enable_cli' => '1',
            'opcache.memory_consumption' => '128',
            'opcache.interned_strings_buffer' => '8',
            'opcache.max_accelerated_files' => '10000',
        ];
    }

    /**
     * Configure the main application window with desktop-appropriate defaults.
     */
    protected function createMainWindow(): void
    {
        Window::open('main')
            ->title(BusinessProfile::name())
            ->width(1280)
            ->height(800)
            ->minWidth(1024)
            ->minHeight(600)
            ->rememberState();
    }

    /**
     * Build a native application menu bar using NativePHP's role-based menus.
     *
     * Available roles: app(), file(), edit(), view(), window(), help()
     * Custom items: label(), link(), separator(), checkbox(), radio()
     */
    protected function createApplicationMenu(): void
    {
        $menus = [
            Menu::app(),
            Menu::file(),
            Menu::edit(),
        ];

        if (config('app.debug')) {
            $menus[] = Menu::view();
        }

        $menus[] = Menu::window();
        $menus[] = Menu::help();

        Menu::create(...$menus);
    }

    protected function registerUpdateListeners(): void
    {
        if (! env('NATIVEPHP_RUNNING', false)) return;

        Event::listen(\Native\Desktop\Events\AutoUpdater\UpdateAvailable::class, function ($event) {
            \Illuminate\Support\Facades\Cache::put('update_download_status', 'available');
            \Illuminate\Support\Facades\Cache::put('latest_version_available', $event->version);

            try {
                \Native\Desktop\Facades\Notification::title('Update Available')
                    ->message("A new inventory system update is available (v{$event->version}). Open the app settings to download it.")
                    ->show();
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Desktop Notification Failed: ' . $e->getMessage());
            }
        });

        Event::listen(\Native\Desktop\Events\AutoUpdater\DownloadProgress::class, function ($event) {
            \Illuminate\Support\Facades\Cache::put('update_download_status', 'downloading');
            \Illuminate\Support\Facades\Cache::put('update_download_progress', round($event->percent));
        });

        Event::listen(\Native\Desktop\Events\AutoUpdater\UpdateDownloaded::class, function () {
            \Illuminate\Support\Facades\Cache::put('update_download_status', 'downloaded');
            \Illuminate\Support\Facades\Cache::put('update_download_progress', 100);

            try {
                \Native\Desktop\Facades\Notification::title('Update Ready')
                    ->message('The new update has been downloaded and is ready to install! Open app settings to restart.')
                    ->show();
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Desktop Notification Failed: ' . $e->getMessage());
            }
        });

        Event::listen(\Native\Desktop\Events\AutoUpdater\UpdateNotAvailable::class, function () {
            \Illuminate\Support\Facades\Cache::put('update_download_status', 'idle');
            \Illuminate\Support\Facades\Cache::forget('latest_version_available');
            \Illuminate\Support\Facades\Cache::forget('update_download_progress');
        });

        Event::listen(\Native\Desktop\Events\AutoUpdater\Error::class, function ($event) {
            \Illuminate\Support\Facades\Cache::put('update_download_status', 'error');
            \Illuminate\Support\Facades\Cache::put('update_error_message', $event->message ?? 'Unknown error');
        });
    }

    protected function checkForUpdatesOnStartup(): void
    {
        if (! env('NATIVEPHP_RUNNING', false)) return;

        try {
            \Native\Desktop\Facades\AutoUpdater::checkForUpdates();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('AutoUpdater startup check failed: ' . $e->getMessage());
        }
    }
}
