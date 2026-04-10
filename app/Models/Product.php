<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable([
    'category_id',
    'product_group',
    'name',
    'slug',
    'sku',
    'brand',
    'variant',
    'description',
    'purchase_price',
    'selling_price',
    'current_stock',
    'reorder_level',
    'unit_of_measure',
    'is_active',
])]
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'purchase_price' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'current_stock' => 'integer',
            'reorder_level' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $product): void {
            if (blank($product->slug) && filled($product->name)) {
                $product->slug = Str::slug(trim($product->name.' '.$product->variant));
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function stockEntries(): HasMany
    {
        return $this->hasMany(StockEntry::class);
    }

    public function salesRecords(): HasMany
    {
        return $this->hasMany(SalesRecord::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeLowStock(Builder $query): Builder
    {
        return $query
            ->where('current_stock', '>', 0)
            ->whereColumn('current_stock', '<=', 'reorder_level');
    }

    public function scopeOutOfStock(Builder $query): Builder
    {
        return $query->where('current_stock', 0);
    }

    public function isLowStock(): bool
    {
        return (! $this->isOutOfStock()) && ($this->current_stock <= $this->reorder_level);
    }

    public function isOutOfStock(): bool
    {
        return $this->current_stock === 0;
    }

    public function stockStatus(): string
    {
        return match (true) {
            $this->isOutOfStock() => 'out_of_stock',
            $this->isLowStock() => 'low_stock',
            default => 'in_stock',
        };
    }
}
