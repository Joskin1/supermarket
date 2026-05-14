<?php

namespace App\Services\Barcode\Providers;

use App\Services\Barcode\BarcodeLookupResult;
use App\Services\Barcode\Contracts\BarcodeLookupProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class UpcItemDbProvider implements BarcodeLookupProviderInterface
{
    protected const BASE_URL = 'https://api.upcitemdb.com/prod/trial/lookup';

    public function name(): string
    {
        return 'upcitemdb';
    }

    public function lookup(string $barcode): ?BarcodeLookupResult
    {
        try {
            $response = Http::timeout((float) config('services.barcode_lookup.timeout_seconds', 2))
                ->connectTimeout((float) config('services.barcode_lookup.connect_timeout_seconds', 1))
                ->withUserAgent(config('app.name', 'Supermarket').' barcode lookup ('.config('app.url').')')
                ->acceptJson()
                ->get(self::BASE_URL, [
                    'upc' => $barcode,
                ]);

            if ($response->failed()) {
                return null;
            }

            $body = $response->json();

            if (((int) ($body['total'] ?? 0)) < 1 || empty($body['items'])) {
                return null;
            }

            $item = $body['items'][0];
            $productName = trim($item['title'] ?? '');

            if ($productName === '') {
                return null;
            }

            return new BarcodeLookupResult(
                barcode: $barcode,
                source: 'api',
                productName: $productName,
                brand: $this->extractBrand($item),
                categoryHint: $this->extractCategory($item),
                description: $this->extractDescription($item),
                apiProvider: $this->name(),
            );
        } catch (Throwable $exception) {
            Log::warning('UPCitemdb barcode lookup failed', [
                'barcode' => $barcode,
                'message' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * @param  array<string, mixed>  $item
     */
    protected function extractBrand(array $item): ?string
    {
        $brand = trim($item['brand'] ?? '');

        return $brand !== '' ? $brand : null;
    }

    /**
     * @param  array<string, mixed>  $item
     */
    protected function extractCategory(array $item): ?string
    {
        $category = trim($item['category'] ?? '');

        return $category !== '' ? $category : null;
    }

    /**
     * @param  array<string, mixed>  $item
     */
    protected function extractDescription(array $item): ?string
    {
        $description = trim($item['description'] ?? '');

        return $description !== '' ? $description : null;
    }
}
