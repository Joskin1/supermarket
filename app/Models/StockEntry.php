<?php

namespace App\Models;

use App\Casts\DateOnlyCast;
use Database\Factories\StockEntryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'product_id',
    'quantity_added',
    'unit_cost_price',
    'unit_selling_price',
    'stock_date',
    'reference',
    'note',
    'created_by',
])]
class StockEntry extends Model
{
    /** @use HasFactory<StockEntryFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'quantity_added' => 'integer',
            'unit_cost_price' => 'decimal:2',
            'unit_selling_price' => 'decimal:2',
            'stock_date' => DateOnlyCast::class,
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
