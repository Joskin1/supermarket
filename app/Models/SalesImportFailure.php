<?php

namespace App\Models;

use App\Casts\DateOnlyCast;
use Database\Factories\SalesImportFailureFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'batch_id',
    'row_number',
    'raw_row',
    'error_messages',
    'product_code',
    'product_name',
    'sales_date',
])]
class SalesImportFailure extends Model
{
    /** @use HasFactory<SalesImportFailureFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'raw_row' => 'array',
            'error_messages' => 'array',
            'sales_date' => DateOnlyCast::class,
        ];
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(SalesImportBatch::class, 'batch_id');
    }
}
