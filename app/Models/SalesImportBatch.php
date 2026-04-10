<?php

namespace App\Models;

use App\Enums\SalesImportBatchStatus;
use Database\Factories\SalesImportBatchFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'batch_code',
    'file_name',
    'file_path',
    'original_file_name',
    'file_hash',
    'uploaded_by',
    'status',
    'sales_date_from',
    'sales_date_to',
    'total_rows',
    'successful_rows',
    'failed_rows',
    'total_quantity_sold',
    'total_sales_amount',
    'notes',
    'processed_at',
])]
class SalesImportBatch extends Model
{
    /** @use HasFactory<SalesImportBatchFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => SalesImportBatchStatus::class,
            'sales_date_from' => 'date',
            'sales_date_to' => 'date',
            'total_rows' => 'integer',
            'successful_rows' => 'integer',
            'failed_rows' => 'integer',
            'total_quantity_sold' => 'integer',
            'total_sales_amount' => 'decimal:2',
            'processed_at' => 'datetime',
        ];
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function salesRecords(): HasMany
    {
        return $this->hasMany(SalesRecord::class, 'batch_id');
    }

    public function failures(): HasMany
    {
        return $this->hasMany(SalesImportFailure::class, 'batch_id');
    }

    public function hasFailures(): bool
    {
        return $this->failed_rows > 0;
    }
}
