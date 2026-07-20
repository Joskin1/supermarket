<?php

namespace App\Support;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BusinessProfile
{
    public static function applicationName(): string
    {
        return (string) config('app.name', 'Inventory Manager');
    }

    public static function name(): string
    {
        return static::settings()?->business_name ?: static::applicationName();
    }

    public static function diagnosticsFileName(): string
    {
        $slug = Str::studly(Str::slug(static::name()) ?: 'Business');

        return $slug.'_Diagnostics_'.now()->format('Y_m_d_His').'.zip';
    }

    public static function logoUrl(): ?string
    {
        $path = static::settings()?->business_logo_path;

        if (blank($path) || ! Storage::disk('local')->exists($path)) {
            return null;
        }

        return Storage::disk('local')->url($path);
    }

    public static function setupUrl(): string
    {
        $settings = static::settings();

        return $settings
            ? url('/admin/system-settings/'.$settings->getKey().'/edit')
            : url('/admin/system-settings');
    }

    public static function requiresSetup(): bool
    {
        $settings = static::settings();

        if (! $settings) {
            return false;
        }

        if (! Schema::hasColumn('system_settings', 'business_profile_completed_at')) {
            return false;
        }

        return blank($settings->business_profile_completed_at);
    }

    public static function settings(): ?SystemSetting
    {
        try {
            if (! Schema::hasTable('system_settings')) {
                return null;
            }

            if (! Schema::hasColumn('system_settings', 'singleton_key')) {
                return SystemSetting::query()->first();
            }

            return SystemSetting::current();
        } catch (\Throwable) {
            return null;
        }
    }
}
