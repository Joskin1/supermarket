<?php

namespace App\Models;

use Database\Factories\DailySalesSummaryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'sales_date',
    'total_transactions_count',
    'total_quantity_sold',
    'total_sales_amount',
    'batches_count',
])]
class DailySalesSummary extends Model
{
    /** @use HasFactory<DailySalesSummaryFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'sales_date' => 'date',
            'total_transactions_count' => 'integer',
            'total_quantity_sold' => 'integer',
            'total_sales_amount' => 'decimal:2',
            'batches_count' => 'integer',
        ];
    }
}
