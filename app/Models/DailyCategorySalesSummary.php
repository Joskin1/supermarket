<?php

namespace App\Models;

use Database\Factories\DailyCategorySalesSummaryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'sales_date',
    'category_id',
    'category_snapshot',
    'total_quantity_sold',
    'total_sales_amount',
    'transactions_count',
])]
class DailyCategorySalesSummary extends Model
{
    /** @use HasFactory<DailyCategorySalesSummaryFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'sales_date' => 'date',
            'total_quantity_sold' => 'integer',
            'total_sales_amount' => 'decimal:2',
            'transactions_count' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
