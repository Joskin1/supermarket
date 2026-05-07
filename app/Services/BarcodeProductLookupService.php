<?php

namespace App\Services;

use App\Models\BarcodeLookupCache;
use App\Models\Product;
use Illuminate\Support\Facades\Http;

class BarcodeProductLookupService
{
    /**
     * @return array<string, mixed>|null
     */
    public function find(string $barcode): ?array
    {
        $barcode = trim($barcode);

        if ($barcode === '') {
            return null;
        }

        $localProduct = Product::query()
            ->with('category:id,name')
            ->where('barcode', $barcode)
            ->first();

        if ($localProduct) {
            return [
                'source' => 'local',
                'barcode' => $barcode,
                'sku' => $localProduct->sku,
                'product_name' => $localProduct->name,
                'brand' => $localProduct->brand,
                'category_hint' => $localProduct->category?->name,
                'product_id' => $localProduct->id,
            ];
        }

        $cached = BarcodeLookupCache::query()
            ->where('barcode', $barcode)
            ->first();

        if ($cached) {
            return $this->cacheToResult($cached);
        }

        return $this->lookupExternally($barcode);
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function lookupExternally(string $barcode): ?array
    {
        foreach ((array) config('services.barcode_lookup.providers', ['open_food_facts']) as $provider) {
            $result = match ($provider) {
                'upcitemdb' => $this->lookupUpcItemDb($barcode),
                default => $this->lookupOpenFoodFacts($barcode),
            };

            if ($result !== null) {
                $cache = BarcodeLookupCache::query()->updateOrCreate(
                    ['barcode' => $barcode],
                    array_merge($result, ['last_found_at' => now()]),
                );

                return $this->cacheToResult($cache);
            }
        }

        return null;
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function lookupOpenFoodFacts(string $barcode): ?array
    {
        $response = Http::timeout(5)
            ->acceptJson()
            ->get("https://world.openfoodfacts.org/api/v2/product/{$barcode}.json", [
                'fields' => 'product_name,brands,categories_tags,categories',
            ]);

        if (! $response->ok() || (int) $response->json('status') !== 1) {
            return null;
        }

        $product = (array) $response->json('product', []);
        $name = $product['product_name'] ?? null;

        if (blank($name)) {
            return null;
        }

        return [
            'provider' => 'open_food_facts',
            'product_name' => trim((string) $name),
            'brand' => filled($product['brands'] ?? null) ? trim((string) $product['brands']) : null,
            'category_hint' => filled($product['categories'] ?? null) ? trim((string) $product['categories']) : null,
            'raw_payload' => $response->json(),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function lookupUpcItemDb(string $barcode): ?array
    {
        $response = Http::timeout(5)
            ->acceptJson()
            ->get('https://api.upcitemdb.com/prod/trial/lookup', [
                'upc' => $barcode,
            ]);

        if (! $response->ok() || (int) $response->json('total', 0) < 1) {
            return null;
        }

        $item = (array) collect($response->json('items', []))->first();
        $name = $item['title'] ?? null;

        if (blank($name)) {
            return null;
        }

        return [
            'provider' => 'upcitemdb',
            'product_name' => trim((string) $name),
            'brand' => filled($item['brand'] ?? null) ? trim((string) $item['brand']) : null,
            'category_hint' => filled($item['category'] ?? null) ? trim((string) $item['category']) : null,
            'raw_payload' => $response->json(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function cacheToResult(BarcodeLookupCache $cache): array
    {
        return [
            'source' => 'external',
            'provider' => $cache->provider,
            'barcode' => $cache->barcode,
            'product_name' => $cache->product_name,
            'brand' => $cache->brand,
            'category_hint' => $cache->category_hint,
        ];
    }
}
