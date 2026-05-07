<?php

namespace App\Services\Barcode;

use App\Models\Product;

/**
 * Normalized result from any barcode lookup source.
 */
final readonly class BarcodeLookupResult
{
    /**
     * @param  'local'|'cached'|'api'|'not_found'  $source
     */
    public function __construct(
        public string $barcode,
        public string $source,
        public ?string $productName = null,
        public ?string $brand = null,
        public ?string $categoryHint = null,
        public ?string $apiProvider = null,
        public ?Product $product = null,
    ) {}

    public function isLocal(): bool
    {
        return $this->source === 'local';
    }

    public function isCached(): bool
    {
        return $this->source === 'cached';
    }

    public function isFromApi(): bool
    {
        return $this->source === 'api';
    }

    public function isNotFound(): bool
    {
        return $this->source === 'not_found';
    }

    public function wasFound(): bool
    {
        return ! $this->isNotFound();
    }
}
