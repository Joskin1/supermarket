<?php

namespace App\Services\Barcode\Contracts;

use App\Services\Barcode\BarcodeLookupResult;

interface BarcodeLookupProviderInterface
{
    /**
     * Look up a barcode using this provider. Returns null if the
     * barcode is not found or the provider is unavailable.
     */
    public function lookup(string $barcode): ?BarcodeLookupResult;

    /**
     * Human-readable name for audit logs and cache source tracking.
     */
    public function name(): string;
}
