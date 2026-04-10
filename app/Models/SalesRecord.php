<?php

namespace App\Models;

use Database\Factories\SalesRecordFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'batch_id',
    'product_id',
    'product_code_snapshot',
    'category_snapshot',
    'product_name_snapshot',
    'unit_price',
    'quantity_sold',
    'total_amount',
    'sales_date',
    'note',
    'created_by',
])]
class SalesRecord extends Model
{
    /** @use HasFactory<SalesRecordFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'quantity_sold' => 'integer',
            'total_amount' => 'decimal:2',
            'sales_date' => 'date',
        ];
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(SalesImportBatch::class, 'batch_id');
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
