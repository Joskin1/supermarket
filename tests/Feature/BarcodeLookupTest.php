<?php

namespace Tests\Feature;

use App\Models\BarcodeLookup;
use App\Models\Category;
use App\Models\Product;
use App\Services\Barcode\BarcodeLookupResult;
use App\Services\Barcode\BarcodeLookupService;
use App\Services\Barcode\BarcodeNormalizer;
use App\Services\Barcode\Contracts\BarcodeLookupProviderInterface;
use App\Services\Barcode\Providers\OpenFoodFactsProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class BarcodeLookupTest extends TestCase
{
    use RefreshDatabase;

    // ---------------------------------------------------------------
    // BarcodeNormalizer
    // ---------------------------------------------------------------

    public function test_normalizer_trims_and_strips_hidden_characters(): void
    {
        $normalizer = new BarcodeNormalizer;

        $this->assertSame('1234567890', $normalizer->normalize("  \u{200B}1234567890\u{FEFF}  "));
    }

    public function test_normalizer_rejects_barcode_shorter_than_minimum(): void
    {
        $normalizer = new BarcodeNormalizer;

        $this->expectException(ValidationException::class);

        $normalizer->normalize('AB');
    }

    public function test_normalizer_rejects_barcode_longer_than_maximum(): void
    {
        $normalizer = new BarcodeNormalizer;

        $this->expectException(ValidationException::class);

        $normalizer->normalize(str_repeat('X', 65));
    }

    public function test_normalizer_accepts_valid_barcode(): void
    {
        $normalizer = new BarcodeNormalizer;

        $this->assertSame('CUSTOM-NG-1234', $normalizer->normalize('CUSTOM-NG-1234'));
    }

    public function test_try_normalize_returns_null_for_invalid_input(): void
    {
        $normalizer = new BarcodeNormalizer;

        $this->assertNull($normalizer->tryNormalize(null));
        $this->assertNull($normalizer->tryNormalize('AB'));
    }

    // ---------------------------------------------------------------
    // BarcodeLookupService — local product found
    // ---------------------------------------------------------------

    public function test_local_product_is_returned_when_barcode_matches(): void
    {
        $product = Product::factory()->create([
            'barcode' => '5901234123457',
        ]);

        $result = app(BarcodeLookupService::class)->lookup('5901234123457');

        $this->assertTrue($result->isLocal());
        $this->assertSame($product->id, $result->product->id);
        $this->assertSame($product->name, $result->productName);
    }

    // ---------------------------------------------------------------
    // BarcodeLookupService — cache hit
    // ---------------------------------------------------------------

    public function test_cached_lookup_is_returned_without_calling_api(): void
    {
        Http::fake(); // Should never be called.

        BarcodeLookup::factory()->create([
            'barcode' => '7891000100103',
            'source' => 'openfoodfacts',
            'product_name' => 'Cached Product',
            'brand' => 'Cached Brand',
        ]);

        $result = app(BarcodeLookupService::class)->lookup('7891000100103');

        $this->assertTrue($result->isCached());
        $this->assertSame('Cached Product', $result->productName);
        $this->assertSame('Cached Brand', $result->brand);

        Http::assertNothingSent();
    }

    // ---------------------------------------------------------------
    // BarcodeLookupService — API hit + caching
    // ---------------------------------------------------------------

    public function test_api_result_is_returned_and_cached(): void
    {
        Http::fake([
            'world.openfoodfacts.org/*' => Http::response([
                'status' => 1,
                'product' => [
                    'product_name' => 'Coca-Cola Classic',
                    'brands' => 'Coca-Cola',
                    'categories_tags' => ['en:beverages', 'en:sodas'],
                ],
            ]),
        ]);

        $result = app(BarcodeLookupService::class)->lookup('5449000000996');

        $this->assertTrue($result->isFromApi());
        $this->assertSame('Coca-Cola Classic', $result->productName);
        $this->assertSame('Coca-Cola', $result->brand);
        $this->assertSame('Sodas', $result->categoryHint);

        // Verify it was cached.
        $cached = BarcodeLookup::query()->where('barcode', '5449000000996')->first();
        $this->assertNotNull($cached);
        $this->assertSame('openfoodfacts', $cached->source);
        $this->assertSame('Coca-Cola Classic', $cached->product_name);
    }

    public function test_open_products_facts_can_fill_lookup_when_food_facts_misses(): void
    {
        config(['services.barcode_lookup.providers' => ['open_food_facts', 'open_products_facts']]);

        Http::fake([
            'world.openfoodfacts.org/*' => Http::response([
                'status' => 0,
                'product' => null,
            ]),
            'world.openproductsfacts.org/*' => Http::response([
                'status' => 1,
                'product' => [
                    'product_name' => 'General Household Product',
                    'brands' => 'Household Brand',
                    'categories' => 'Household supplies',
                ],
            ]),
        ]);

        $result = app(BarcodeLookupService::class)->lookup('6180200000124');

        $this->assertTrue($result->isFromApi());
        $this->assertSame('openproductsfacts', $result->apiProvider);
        $this->assertSame('General Household Product', $result->productName);

        $this->assertDatabaseHas('barcode_lookups', [
            'barcode' => '6180200000124',
            'source' => 'openproductsfacts',
            'product_name' => 'General Household Product',
        ]);
    }

    public function test_open_beauty_facts_can_fill_lookup_for_personal_care_items(): void
    {
        config(['services.barcode_lookup.providers' => ['open_food_facts', 'open_products_facts', 'open_beauty_facts']]);

        Http::fake([
            'world.openfoodfacts.org/*' => Http::response([
                'status' => 0,
                'product' => null,
            ]),
            'world.openproductsfacts.org/*' => Http::response([
                'status' => 0,
                'product' => null,
            ]),
            'world.openbeautyfacts.org/*' => Http::response([
                'status' => 1,
                'product' => [
                    'product_name' => 'Body Lotion',
                    'brands' => 'Care Brand',
                    'categories' => 'Personal care',
                ],
            ]),
        ]);

        $result = app(BarcodeLookupService::class)->lookup('3560070791460');

        $this->assertTrue($result->isFromApi());
        $this->assertSame('openbeautyfacts', $result->apiProvider);
        $this->assertSame('Body Lotion', $result->productName);
    }

    // ---------------------------------------------------------------
    // BarcodeLookupService — not found
    // ---------------------------------------------------------------

    public function test_not_found_is_returned_when_no_source_has_the_barcode(): void
    {
        config(['services.barcode_lookup.providers' => ['open_food_facts']]);

        Http::fake([
            'world.openfoodfacts.org/*' => Http::response([
                'status' => 0,
                'product' => null,
            ]),
        ]);

        $result = app(BarcodeLookupService::class)->lookup('0000000000000');

        $this->assertTrue($result->isNotFound());
        $this->assertNull($result->productName);
    }

    public function test_recent_not_found_result_is_cached_without_calling_api_again(): void
    {
        config(['services.barcode_lookup.providers' => ['open_food_facts']]);

        Http::fake([
            'world.openfoodfacts.org/*' => Http::response([
                'status' => 0,
                'product' => null,
            ]),
        ]);

        $first = app(BarcodeLookupService::class)->lookup('0000000000000');

        $this->assertTrue($first->isNotFound());
        $this->assertDatabaseHas('barcode_lookups', [
            'barcode' => '0000000000000',
            'source' => 'not_found',
        ]);
        Http::assertSentCount(1);

        Http::fake();

        $second = app(BarcodeLookupService::class)->lookup('0000000000000');

        $this->assertTrue($second->isNotFound());
        Http::assertNothingSent();
    }

    public function test_stale_not_found_cache_is_retried_when_provider_list_changes(): void
    {
        BarcodeLookup::factory()->create([
            'barcode' => '6180200000124',
            'source' => 'not_found',
            'product_name' => null,
            'raw_response' => [
                'providers' => ['openfoodfacts'],
            ],
            'looked_up_at' => now(),
        ]);

        config(['services.barcode_lookup.providers' => ['open_food_facts', 'open_products_facts']]);

        Http::fake([
            'world.openfoodfacts.org/*' => Http::response([
                'status' => 0,
                'product' => null,
            ]),
            'world.openproductsfacts.org/*' => Http::response([
                'status' => 1,
                'product' => [
                    'product_name' => 'Retried Product',
                    'brands' => 'Fresh Source',
                ],
            ]),
        ]);

        $result = app(BarcodeLookupService::class)->lookup('6180200000124');

        $this->assertTrue($result->isFromApi());
        $this->assertSame('Retried Product', $result->productName);
        Http::assertSentCount(2);
    }

    // ---------------------------------------------------------------
    // OpenFoodFactsProvider edge cases
    // ---------------------------------------------------------------

    public function test_openfoodfacts_returns_null_on_timeout(): void
    {
        Http::fake([
            'world.openfoodfacts.org/*' => fn () => throw new \Illuminate\Http\Client\ConnectionException('Timeout'),
        ]);

        $provider = new OpenFoodFactsProvider;

        $this->assertNull($provider->lookup('5449000000996'));
    }

    public function test_openfoodfacts_returns_null_for_empty_product_name(): void
    {
        Http::fake([
            'world.openfoodfacts.org/*' => Http::response([
                'status' => 1,
                'product' => [
                    'product_name' => '',
                    'brands' => 'SomeBrand',
                ],
            ]),
        ]);

        $provider = new OpenFoodFactsProvider;

        $this->assertNull($provider->lookup('5449000000996'));
    }
}
