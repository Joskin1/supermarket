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

    protected const TIMEOUT_SECONDS = 5;

    protected const RETRY_TIMES = 2;

    protected const RETRY_DELAY_MS = 200;

    public function name(): string
    {
        return 'openfoodfacts';
    }

    public function lookup(string $barcode): ?BarcodeLookupResult
    {
        try {
            $response = Http::timeout(self::TIMEOUT_SECONDS)
                ->retry(self::RETRY_TIMES, self::RETRY_DELAY_MS)
                ->acceptJson()
                ->get(self::BASE_URL.$barcode.'.json', [
                    'fields' => 'product_name,brands,categories_tags,code',
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
}
