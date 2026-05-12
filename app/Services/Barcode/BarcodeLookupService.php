<?php

namespace App\Services\Barcode;

use App\Models\BarcodeLookup;
use App\Models\Product;
use App\Services\Barcode\Contracts\BarcodeLookupProviderInterface;
use App\Services\Barcode\Providers\OpenFoodFactsProvider;
use App\Services\Barcode\Providers\UpcItemDbProvider;

class BarcodeLookupService
{
    /**
     * Lookup order:
     * 1. Local product database
     * 2. Barcode lookup cache (previous API results)
     * 3. External API providers (OpenFoodFacts → UPCitemdb → future fallbacks)
     */
    public function lookup(string $barcode): BarcodeLookupResult
    {
        $normalized = app(BarcodeNormalizer::class)->normalize($barcode);

        // Step 1: Check local product database.
        $product = Product::query()->byBarcode($normalized)->first();

        if ($product) {
            return new BarcodeLookupResult(
                barcode: $normalized,
                source: 'local',
                productName: $product->name,
                brand: $product->brand,
                categoryHint: $product->category?->name,
                description: $product->description,
                product: $product,
            );
        }

        // Step 2: Check the lookup cache.
        $cached = BarcodeLookup::query()
            ->where('barcode', $normalized)
            ->first();

        if ($cached) {
            return new BarcodeLookupResult(
                barcode: $normalized,
                source: 'cached',
                productName: $cached->product_name,
                brand: $cached->brand,
                categoryHint: $cached->category_hint,
                apiProvider: $cached->source,
            );
        }

        // Step 3: Call external API providers in order.
        foreach ($this->providers() as $provider) {
            $result = $provider->lookup($normalized);

            if ($result !== null && $result->wasFound()) {
                $this->cacheResult($normalized, $provider, $result);

                return $result;
            }
        }

        return new BarcodeLookupResult(
            barcode: $normalized,
            source: 'not_found',
        );
    }

    /**
     * Registered API providers in lookup priority order.
     * Configurable via BARCODE_LOOKUP_PROVIDERS env variable.
     *
     * @return array<int, BarcodeLookupProviderInterface>
     */
    protected function providers(): array
    {
        $configured = (array) config('services.barcode_lookup.providers', ['open_food_facts']);

        $available = [
            'open_food_facts' => OpenFoodFactsProvider::class,
            'upcitemdb' => UpcItemDbProvider::class,
        ];

        $providers = [];

        foreach ($configured as $key) {
            if (isset($available[$key])) {
                $providers[] = app($available[$key]);
            }
        }

        // Always include at least OpenFoodFacts.
        if ($providers === []) {
            $providers[] = app(OpenFoodFactsProvider::class);
        }

        return $providers;
    }

    protected function cacheResult(
        string $barcode,
        BarcodeLookupProviderInterface $provider,
        BarcodeLookupResult $result,
    ): void {
        BarcodeLookup::query()->updateOrCreate(
            ['barcode' => $barcode],
            [
                'source' => $provider->name(),
                'product_name' => $result->productName,
                'brand' => $result->brand,
                'category_hint' => $result->categoryHint,
                'looked_up_at' => now(),
            ],
        );
    }
}

