<?php

namespace App\Models;

use Database\Factories\DailyProductSalesSummaryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'sales_date',
    'product_id',
    'product_code_snapshot',
    'product_name_snapshot',
    'category_id',
    'category_snapshot',
    'total_quantity_sold',
    'total_sales_amount',
    'transactions_count',
])]
class DailyProductSalesSummary extends Model
{
    /** @use HasFactory<DailyProductSalesSummaryFactory> */
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

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
