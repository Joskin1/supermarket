<?php

namespace App\Services\Barcode\Providers;

use App\Services\Barcode\BarcodeLookupResult;
use App\Services\Barcode\Contracts\BarcodeLookupProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class OpenFoodFactsProvider implements BarcodeLookupProviderInterface
{
    protected const BASE_URL = 'https://world.openfoodfacts.org/api/v2/product/';

    public function name(): string
    {
        return 'openfoodfacts';
    }

    protected function baseUrl(): string
    {
        return static::BASE_URL;
    }

    public function lookup(string $barcode): ?BarcodeLookupResult
    {
        try {
            $response = Http::timeout((float) config('services.barcode_lookup.timeout_seconds', 2))
                ->connectTimeout((float) config('services.barcode_lookup.connect_timeout_seconds', 1))
                ->withUserAgent(config('app.name', 'Inventory Manager').' barcode lookup ('.config('app.url').')')
                ->acceptJson()
                ->get($this->baseUrl().$barcode.'.json', [
                    'fields' => 'product_name,brands,categories,categories_tags,generic_name,code',
                ]);

            if ($response->failed()) {
                return null;
            }

            $body = $response->json();

            if (($body['status'] ?? 0) !== 1 || empty($body['product'])) {
                return null;
            }

            $product = $body['product'];

            $productName = trim($product['product_name'] ?? '');

            if ($productName === '') {
                return null;
            }

            return new BarcodeLookupResult(
                barcode: $barcode,
                source: 'api',
                productName: $productName,
                brand: $this->extractBrand($product),
                categoryHint: $this->extractCategory($product),
                description: $this->extractDescription($product),
                apiProvider: $this->name(),
            );
        } catch (Throwable $exception) {
            Log::warning('OpenFoodFacts barcode lookup failed', [
                'barcode' => $barcode,
                'message' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * @param  array<string, mixed>  $product
     */
    protected function extractBrand(array $product): ?string
    {
        $brand = trim($product['brands'] ?? '');

        return $brand !== '' ? $brand : null;
    }

    /**
     * @param  array<string, mixed>  $product
     */
    protected function extractCategory(array $product): ?string
    {
        $category = trim((string) ($product['categories'] ?? ''));

        if ($category !== '') {
            return $category;
        }

        $tags = $product['categories_tags'] ?? [];

        if (! is_array($tags) || $tags === []) {
            return null;
        }

        // Use the last (most specific) category tag and humanize it.
        $lastTag = end($tags);

        // Tags look like "en:beverages" — strip the language prefix.
        $category = preg_replace('/^[a-z]{2}:/', '', $lastTag);

        return ucfirst(str_replace('-', ' ', $category));
    }

    /**
     * @param  array<string, mixed>  $product
     */
    protected function extractDescription(array $product): ?string
    {
        $description = trim((string) ($product['generic_name'] ?? ''));

        return $description !== '' ? $description : null;
    }
}
