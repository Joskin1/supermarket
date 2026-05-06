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
    'business_timezone',
    'currency_code',
    'low_stock_contact_email',
    'receipt_footer',
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
            'business_timezone' => config('app.timezone'),
            'currency_code' => 'NGN',
            'low_stock_contact_email' => null,
            'receipt_footer' => null,
        ];
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
