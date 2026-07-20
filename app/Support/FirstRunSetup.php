<?php

namespace App\Support;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

class FirstRunSetup
{
    public const LOCAL_OWNER_EMAIL = 'owner@local.app';

    public static function isComplete(): bool
    {
        try {
            if (! Schema::hasTable('users') || ! Schema::hasTable('roles')) {
                return false;
            }

            return User::query()
                ->whereHas('roles', fn ($query) => $query->whereIn('name', [
                    RoleEnum::SUDO->value,
                    RoleEnum::ADMIN->value,
                ]))
                ->exists();
        } catch (\Throwable) {
            return false;
        }
    }

    public static function needsSetup(): bool
    {
        return ! static::isComplete();
    }

    public static function setupUrl(): string
    {
        return route('setup');
    }
}
