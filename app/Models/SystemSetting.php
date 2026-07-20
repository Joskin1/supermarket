<?php

namespace App\Models;

use Database\Factories\SystemSettingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'singleton_key',
    'business_name',
    'business_logo_path',
    'business_type',
    'business_timezone',
    'currency_code',
    'low_stock_contact_email',
    'business_phone',
    'business_email',
    'business_address',
    'receipt_footer',
    'business_profile_completed_at',
])]
class SystemSetting extends Model
{
    /** @use HasFactory<SystemSettingFactory> */
    use HasFactory;

    public const SINGLETON_KEY = 'current';

    public static function defaults(): array
    {
        return [
            'singleton_key' => self::SINGLETON_KEY,
            'business_name' => config('app.name'),
            'business_logo_path' => null,
            'business_type' => null,
            'business_timezone' => config('app.timezone'),
            'currency_code' => 'NGN',
            'low_stock_contact_email' => null,
            'business_phone' => null,
            'business_email' => null,
            'business_address' => null,
            'receipt_footer' => null,
            'business_profile_completed_at' => null,
        ];
    }

    protected function casts(): array
    {
        return [
            'business_profile_completed_at' => 'immutable_datetime',
        ];
    }

    public function markBusinessProfileComplete(): void
    {
        if (blank($this->business_profile_completed_at)) {
            $this->forceFill([
                'business_profile_completed_at' => now(),
            ])->save();
        }
    }

    public function scopeCurrentRecord(Builder $query): Builder
    {
        return $query->where('singleton_key', self::SINGLETON_KEY);
    }

    public static function current(): self
    {
        return static::query()->currentRecord()->firstOrCreate(
            ['singleton_key' => self::SINGLETON_KEY],
            static::defaults(),
        );
    }
}
