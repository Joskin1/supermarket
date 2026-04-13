<?php

namespace App\Models;

use App\Casts\DateOnlyCast;
use Database\Factories\StockAdjustmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'product_id',
    'quantity_change',
    'previous_stock',
    'new_stock',
    'counted_stock',
    'reason',
    'reference',
    'note',
    'adjustment_date',
    'adjusted_by',
])]
class StockAdjustment extends Model
{
    /** @use HasFactory<StockAdjustmentFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'quantity_change' => 'integer',
            'previous_stock' => 'integer',
            'new_stock' => 'integer',
            'counted_stock' => 'integer',
            'adjustment_date' => DateOnlyCast::class,
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function adjuster(): BelongsTo
    {
        return $this->belongsTo(User::class, 'adjusted_by');
    }
}
