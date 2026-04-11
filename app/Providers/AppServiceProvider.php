<?php

namespace App\Providers;

use App\Enums\RoleEnum;
use App\Models\Category;
use App\Models\Product;
use App\Models\SalesImportBatch;
use App\Models\SalesRecord;
use App\Models\StockEntry;
use App\Models\User;
use App\Policies\CategoryPolicy;
use App\Policies\ProductPolicy;
use App\Policies\SalesImportBatchPolicy;
use App\Policies\SalesRecordPolicy;
use App\Policies\StockEntryPolicy;
use App\Policies\UserPolicy;
use Carbon\CarbonImmutable;
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
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }

    protected function configureAuthorization(): void
    {
        Gate::policy(Category::class, CategoryPolicy::class);
        Gate::policy(Product::class, ProductPolicy::class);
        Gate::policy(SalesImportBatch::class, SalesImportBatchPolicy::class);
        Gate::policy(SalesRecord::class, SalesRecordPolicy::class);
        Gate::policy(StockEntry::class, StockEntryPolicy::class);
        Gate::policy(User::class, UserPolicy::class);

        Gate::before(function (User $user, string $ability): ?bool {
            return $user->hasRole(RoleEnum::SUDO->value) ? true : null;
        });
    }
}
