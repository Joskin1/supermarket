<?php

namespace App\Services\Barcode;

use App\Models\BarcodeLookup;
use App\Models\Product;
use App\Services\Barcode\Contracts\BarcodeLookupProviderInterface;
use App\Services\Barcode\Providers\OpenBeautyFactsProvider;
use App\Services\Barcode\Providers\OpenFoodFactsProvider;
use App\Services\Barcode\Providers\OpenProductsFactsProvider;
use App\Services\Barcode\Providers\UpcItemDbProvider;
use Illuminate\Support\Facades\Log;

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
            if ($cached->source === 'not_found') {
                if (
                    $cached->looked_up_at?->greaterThanOrEqualTo(now()->subDays($this->notFoundCacheDays()))
                    && ($cached->raw_response['providers'] ?? null) === $this->providerNames()
                ) {
                    return new BarcodeLookupResult(
                        barcode: $normalized,
                        source: 'not_found',
                    );
                }

                $cached->delete();
            } else {
                if (blank($cached->product_name)) {
                    return new BarcodeLookupResult(
                        barcode: $normalized,
                        source: 'not_found',
                    );
                }

                return new BarcodeLookupResult(
                    barcode: $normalized,
                    source: 'cached',
                    productName: $cached->product_name,
                    brand: $cached->brand,
                    categoryHint: $cached->category_hint,
                    apiProvider: $cached->source,
                );
            }
        }

        // Step 3: Call external API providers in order.
        foreach ($this->providers() as $provider) {
            $startedAt = microtime(true);
            $result = $provider->lookup($normalized);

            Log::info('Barcode provider lookup completed', [
                'barcode' => $normalized,
                'provider' => $provider->name(),
                'found' => $result?->wasFound() === true,
                'duration_ms' => (int) round((microtime(true) - $startedAt) * 1000),
            ]);

            if ($result !== null && $result->wasFound()) {
                $this->cacheResult($normalized, $provider, $result);

                return $result;
            }
        }

        $this->cacheNotFound($normalized);

        return new BarcodeLookupResult(
            barcode: $normalized,
            source: 'not_found',
        );
    }

    protected function notFoundCacheDays(): int
    {
        return max(1, (int) config('services.barcode_lookup.not_found_cache_days', 7));
    }

    protected function cacheNotFound(string $barcode): void
    {
        BarcodeLookup::query()->updateOrCreate(
            ['barcode' => $barcode],
            [
                'source' => 'not_found',
                'product_name' => null,
                'brand' => null,
                'category_hint' => null,
                'raw_response' => [
                    'providers' => $this->providerNames(),
                ],
                'looked_up_at' => now(),
            ],
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
            'open_products_facts' => OpenProductsFactsProvider::class,
            'open_beauty_facts' => OpenBeautyFactsProvider::class,
            'upcitemdb' => UpcItemDbProvider::class,
        ];

        $providers = [];

        foreach ($configured as $key) {
            $key = trim((string) $key);

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

    /**
     * @return array<int, string>
     */
    protected function providerNames(): array
    {
        return array_map(
            fn (BarcodeLookupProviderInterface $provider): string => $provider->name(),
            $this->providers(),
        );
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
                'raw_response' => null,
                'looked_up_at' => now(),
            ],
        );
    }
}
