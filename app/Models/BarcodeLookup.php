<?php

namespace App\Models;

use Database\Factories\BarcodeLookupFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'barcode',
    'source',
    'product_name',
    'brand',
    'category_hint',
    'raw_response',
    'looked_up_at',
])]
class BarcodeLookup extends Model
{
    /** @use HasFactory<BarcodeLookupFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'raw_response' => 'array',
            'looked_up_at' => 'datetime',
        ];
    }
}
