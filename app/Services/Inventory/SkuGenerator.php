<?php

namespace App\Services\Inventory;

use App\Models\Product;
use Illuminate\Support\Str;

class SkuGenerator
{
    /**
     * Generate a unique, human-readable SKU.
     *
     * Format: SM-{YYMMDD}-{4-char uppercase alphanumeric}
     * Example: SM-260507-X7K2
     *
     * The date segment makes SKUs sortable by creation date.
     * The random suffix guarantees uniqueness via collision check.
     */
    public function generate(): string
    {
        do {
            $sku = sprintf(
                'SM-%s-%s',
                now()->format('ymd'),
                Str::upper(Str::random(4)),
            );
        } while (Product::withTrashed()->where('sku', $sku)->exists());

        return $sku;
    }
}
