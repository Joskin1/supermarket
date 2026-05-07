<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'barcode',
    'provider',
    'product_name',
    'brand',
    'category_hint',
    'raw_payload',
    'last_found_at',
])]
class BarcodeLookupCache extends Model
{
    protected function casts(): array
    {
        return [
            'raw_payload' => 'array',
            'last_found_at' => 'datetime',
        ];
    }
}
