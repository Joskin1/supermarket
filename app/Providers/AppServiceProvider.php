<?php

namespace App\Providers;

use App\Models\ActivityLog;
use App\Models\BackupRun;
use App\Models\Category;
use App\Models\Product;
use App\Models\SalesImportBatch;
use App\Models\SalesRecord;
use App\Models\StockAdjustment;
use App\Models\StockEntry;
use App\Models\SystemSetting;
use App\Models\User;
use App\Policies\ActivityLogPolicy;
use App\Policies\BackupRunPolicy;
use App\Policies\CategoryPolicy;
use App\Policies\ProductPolicy;
use App\Policies\SalesImportBatchPolicy;
use App\Policies\SalesRecordPolicy;
use App\Policies\StockAdjustmentPolicy;
use App\Policies\StockEntryPolicy;
use App\Policies\SystemSettingPolicy;
use App\Policies\UserPolicy;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Livewire\Compiler\CacheManager as LivewireCacheManager;
use Livewire\Compiler\Compiler as LivewireCompiler;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->extend('livewire.compiler', function (): LivewireCompiler {
            return new LivewireCompiler(
                new LivewireCacheManager(
                    storage_path('framework/views/livewire/'.md5(base_path()))
                )
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureAuthorization();

        // Tune SQLite for maximum speed and concurrent read/writes (extremely important on Windows!)
        \Illuminate\Support\Facades\Event::listen(\Illuminate\Database\Events\ConnectionEstablished::class, function ($event) {
            if ($event->connection->getDriverName() === 'sqlite') {
                $event->connection->unprepared("
                    PRAGMA journal_mode = WAL;
                    PRAGMA synchronous = NORMAL;
                    PRAGMA cache_size = -2000;
                    PRAGMA temp_store = MEMORY;
                    PRAGMA busy_timeout = 5000;
                ");
            }
        });

        // Desktop app runs on localhost — no HTTPS forcing needed.
        // The NativePHP Electron shell handles the local server.
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        Model::shouldBeStrict(! app()->isProduction());

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : Password::min(8),
        );
    }

    protected function configureAuthorization(): void
    {
        Gate::policy(Category::class, CategoryPolicy::class);
        Gate::policy(ActivityLog::class, ActivityLogPolicy::class);
        Gate::policy(BackupRun::class, BackupRunPolicy::class);
        Gate::policy(Product::class, ProductPolicy::class);
        Gate::policy(SalesImportBatch::class, SalesImportBatchPolicy::class);
        Gate::policy(SalesRecord::class, SalesRecordPolicy::class);
        Gate::policy(StockAdjustment::class, StockAdjustmentPolicy::class);
        Gate::policy(StockEntry::class, StockEntryPolicy::class);
        Gate::policy(SystemSetting::class, SystemSettingPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
    }
}
