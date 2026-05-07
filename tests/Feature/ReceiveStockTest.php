<?php

namespace Tests\Feature;

use App\Enums\RoleEnum;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ReceiveStockTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_users_can_access_the_receive_stock_page(): void
    {
        $this->seed(RoleSeeder::class);

        $admin = User::factory()->create(array_merge(
            ['email_verified_at' => now()],
            $this->confirmedTwoFactorAttributes(),
        ));
        $admin->assignRole(RoleEnum::ADMIN->value);

        $this->actingAs($admin)
            ->get('/admin/receive-stock')
            ->assertOk();
    }

    public function test_sudo_users_can_access_the_receive_stock_page(): void
    {
        $sudo = $this->makeSudo(array_merge(
            ['email_verified_at' => now()],
            $this->confirmedTwoFactorAttributes(),
        ));

        $this->actingAs($sudo)
            ->get('/admin/receive-stock')
            ->assertOk();
    }

    public function test_users_without_roles_cannot_access_the_receive_stock_page(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user)
            ->get('/admin/receive-stock')
            ->assertForbidden();
    }

    public function test_scanning_an_existing_product_transitions_to_product_found_state(): void
    {
        Http::fake();

        $admin = $this->makeAdmin();
        $product = Product::factory()->create(['barcode' => '5901234123457']);

        $this->actingAs($admin);

        $response = $this->livewireTest()
            ->set('barcode', '5901234123457')
            ->call('scanBarcode');

        // Verify state transitioned to 'product_found'.
        $this->assertSame('product_found', $response->get('state'));
        $this->assertSame($product->id, $response->get('foundProductId'));

        Http::assertNothingSent();
    }

    public function test_adding_stock_to_existing_product_creates_stock_entry(): void
    {
        $admin = $this->makeAdmin();
        $product = Product::factory()->create([
            'barcode' => '5901234123457',
            'current_stock' => 10,
        ]);

        $this->actingAs($admin);

        $component = $this->livewireTest();

        // Scan the barcode.
        $component->set('barcode', '5901234123457')
            ->call('scanBarcode');

        // Fill stock details and submit.
        $component
            ->set('quantityAdded', '25')
            ->set('unitCostPrice', '500.00')
            ->set('unitSellingPrice', '750.00')
            ->set('stockDate', now()->toDateString())
            ->call('addStockToExisting');

        // Verify stock was added.
        $this->assertDatabaseHas('stock_entries', [
            'product_id' => $product->id,
            'quantity_added' => 25,
            'created_by' => $admin->id,
        ]);

        // Verify product stock increased.
        $this->assertSame(35, $product->fresh()->current_stock);

        // Verify state reset.
        $this->assertSame('scanning', $component->get('state'));
    }

    public function test_creating_a_new_product_from_barcode_scan_creates_product_and_stock(): void
    {
        Http::fake([
            'world.openfoodfacts.org/*' => Http::response(['status' => 0]),
        ]);

        $admin = $this->makeAdmin();
        $category = Category::factory()->create();

        $this->actingAs($admin);

        $component = $this->livewireTest();

        // Scan an unknown barcode.
        $component->set('barcode', '0000999999999')
            ->call('scanBarcode');

        $this->assertSame('manual_entry', $component->get('state'));

        // Fill product + stock details.
        $component
            ->set('newProductName', 'Test New Product')
            ->set('newProductSku', 'TST-NEW-001')
            ->set('newProductCategoryId', $category->id)
            ->set('newProductPurchasePrice', '1000.00')
            ->set('newProductSellingPrice', '1500.00')
            ->set('quantityAdded', '50')
            ->set('unitCostPrice', '1000.00')
            ->set('unitSellingPrice', '1500.00')
            ->set('stockDate', now()->toDateString())
            ->call('createProductAndAddStock');

        // Verify product created.
        $this->assertDatabaseHas('products', [
            'barcode' => '0000999999999',
            'name' => 'Test New Product',
            'sku' => 'TST-NEW-001',
        ]);

        // Verify stock entry.
        $newProduct = Product::query()->where('barcode', '0000999999999')->first();
        $this->assertNotNull($newProduct);
        $this->assertSame(50, $newProduct->current_stock);

        // Verify state reset.
        $this->assertSame('scanning', $component->get('state'));
    }

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------

    protected function makeAdmin(): User
    {
        $this->seed(RoleSeeder::class);

        $user = User::factory()->create(array_merge(
            ['email_verified_at' => now()],
            $this->confirmedTwoFactorAttributes(),
        ));
        $user->assignRole(RoleEnum::ADMIN->value);

        return $user;
    }

    protected function livewireTest()
    {
        return \Livewire\Livewire::test(\App\Filament\Pages\ReceiveStock::class);
    }
}
