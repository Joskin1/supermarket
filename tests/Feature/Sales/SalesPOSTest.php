<?php

namespace Tests\Feature\Sales;

use App\Enums\SalesImportBatchStatus;
use App\Filament\Pages\SalesPOS;
use App\Models\Product;
use App\Models\SalesImportBatch;
use App\Models\SalesRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SalesPOSTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthorized_users_cannot_access_pos_terminal(): void
    {
        $user = User::factory()->create(); // No special POS permissions
        
        // Assert that the page checks authorization correctly
        $this->actingAs($user);
        
        $this->assertFalse(SalesPOS::canAccess());
    }

    public function test_authorized_users_can_load_pos_terminal(): void
    {
        $user = User::factory()->create();
        
        // Grant permissions by associating to a role/permission that allows SalesImportBatch viewing
        // In this app, auth()->user()?->can('viewAny', SalesImportBatch::class) dictates access.
        // Let's mock the policy or just grant a direct role.
        // Since we are using Spatie Permissions, let's mock or create the permission.
        $this->actingAs($user);
        
        // Let's create a Super Admin or user with access
        $user->givePermissionTo('view_any_sales::import::batch'); // Grant Filament shield permission

        Livewire::test(SalesPOS::class)
            ->assertOk()
            ->assertViewIs('filament.pages.sales-pos')
            ->assertSet('scanQuery', '')
            ->assertSet('cart', []);
    }

    public function test_scanning_a_valid_barcode_retrieves_product_and_focuses_quantity(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('view_any_sales::import::batch');
        $this->actingAs($user);

        $product = Product::factory()->create([
            'barcode' => '7891234567890',
            'sku' => 'PROD-POS-1',
            'selling_price' => 250.50,
            'current_stock' => 15,
        ]);

        Livewire::test(SalesPOS::class)
            ->set('scanQuery', '7891234567890')
            ->call('updatedScanQuery')
            ->assertSet('scannedProduct.id', $product->id)
            ->assertSet('scannedProduct.name', $product->name)
            ->assertSet('scannedProduct.selling_price', 250.50)
            ->assertSet('quantity', 1)
            ->assertDispatched('focus-quantity');
    }

    public function test_scanning_an_unknown_barcode_shows_warning_notification(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('view_any_sales::import::batch');
        $this->actingAs($user);

        Livewire::test(SalesPOS::class)
            ->set('scanQuery', '9999999999999')
            ->call('updatedScanQuery')
            ->assertSet('scanQuery', '')
            ->assertSet('scannedProduct', null)
            ->assertDispatched('focus-scan');
    }

    public function test_live_search_fuzzy_lookup_and_selection(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('view_any_sales::import::batch');
        $this->actingAs($user);

        $product = Product::factory()->create([
            'name' => 'Fuzzy Geloo Drink',
            'sku' => 'GEL-FUZZY-123',
            'barcode' => '54321',
            'selling_price' => 80.00,
        ]);

        Livewire::test(SalesPOS::class)
            // Type fuzzy name search
            ->set('scanQuery', 'Geloo')
            ->call('updatedScanQuery')
            // Assert that results list is populated
            ->assertCount('searchResults', 1)
            ->assertSet('searchResults.0.name', 'Fuzzy Geloo Drink')
            
            // Choose the autocomplete item
            ->call('selectSearchResult', 0)
            ->assertSet('searchResults', [])
            ->assertSet('scanQuery', '')
            ->assertSet('scannedProduct.id', $product->id)
            ->assertDispatched('focus-quantity');
    }

    public function test_scanning_a_product_already_in_cart_increments_its_quantity_automatically(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('view_any_sales::import::batch');
        $this->actingAs($user);

        $product = Product::factory()->create([
            'barcode' => '7891234567890',
            'selling_price' => 100.00,
        ]);

        Livewire::test(SalesPOS::class)
            // 1. First scan
            ->set('scanQuery', '7891234567890')
            ->call('updatedScanQuery')
            ->set('quantity', 2)
            ->call('addToCart')
            ->assertSet('cart.' . $product->id . '.quantity', 2)
            
            // 2. Second scan of the same barcode (auto-increment!)
            ->set('scanQuery', '7891234567890')
            ->call('updatedScanQuery')
            ->assertSet('cart.' . $product->id . '.quantity', 3)
            ->assertSet('cart.' . $product->id . '.total', 300.00)
            ->assertSet('scanQuery', '')
            ->assertDispatched('focus-scan');
    }

    public function test_cashier_can_manipulate_cart_items(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('view_any_sales::import::batch');
        $this->actingAs($user);

        $product1 = Product::factory()->create(['barcode' => '111111', 'selling_price' => 10]);
        $product2 = Product::factory()->create(['barcode' => '222222', 'selling_price' => 20]);

        Livewire::test(SalesPOS::class)
            // Add product 1 (qty 2)
            ->set('scanQuery', '111111')
            ->call('updatedScanQuery')
            ->set('quantity', 2)
            ->call('addToCart')
            
            // Add product 2 (qty 1)
            ->set('scanQuery', '222222')
            ->call('updatedScanQuery')
            ->set('quantity', 1)
            ->call('addToCart')

            // Assert cart totals
            ->assertCount('cart', 2)
            ->assertSet('gross_total', 40.00)
            ->assertSet('gross_quantity', 3)

            // Update quantity of product 2 directly to 3
            ->call('updateQuantity', $product2->id, 3)
            ->assertSet('cart.' . $product2->id . '.quantity', 3)
            ->assertSet('gross_total', 80.00)

            // Remove product 1 from cart
            ->call('removeFromCart', $product1->id)
            ->assertCount('cart', 1)
            ->assertSet('gross_total', 60.00);
    }

    public function test_checkout_saves_batch_records_and_decrements_product_inventory(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('view_any_sales::import::batch');
        $this->actingAs($user);

        $product1 = Product::factory()->create([
            'sku' => 'SKU-POS-CHECK-1',
            'barcode' => '123456',
            'selling_price' => 50.00,
            'current_stock' => 100,
        ]);
        $product2 = Product::factory()->create([
            'sku' => 'SKU-POS-CHECK-2',
            'barcode' => '654321',
            'selling_price' => 15.00,
            'current_stock' => 20,
        ]);

        Livewire::test(SalesPOS::class)
            // Scan and add product 1 (qty 2)
            ->set('scanQuery', '123456')
            ->call('updatedScanQuery')
            ->set('quantity', 2)
            ->call('addToCart')

            // Scan and add product 2 (qty 5)
            ->set('scanQuery', '654321')
            ->call('updatedScanQuery')
            ->set('quantity', 5)
            ->call('addToCart')

            // Log session note
            ->set('notes', 'POS register #1 checkout test')
            
            // Confirm Checkout!
            ->call('checkout')
            ->assertSet('cart', [])
            ->assertDispatched('focus-scan');

        // Assert database creations
        $this->assertDatabaseHas('sales_import_batches', [
            'uploaded_by' => $user->id,
            'status' => SalesImportBatchStatus::PROCESSED->value,
            'total_rows' => 2,
            'successful_rows' => 2,
            'failed_rows' => 0,
            'total_quantity_sold' => 7,
            'total_sales_amount' => 175.00,
            'notes' => 'POS register #1 checkout test',
        ]);

        $batch = SalesImportBatch::where('notes', 'POS register #1 checkout test')->first();
        $this->assertNotNull($batch);
        $this->assertTrue(str_starts_with($batch->batch_code, 'POS-'));

        // Assert sales record logs
        $this->assertDatabaseHas('sales_records', [
            'batch_id' => $batch->id,
            'product_id' => $product1->id,
            'product_code_snapshot' => 'SKU-POS-CHECK-1',
            'unit_price' => 50.00,
            'quantity_sold' => 2,
            'total_amount' => 100.00,
        ]);

        $this->assertDatabaseHas('sales_records', [
            'batch_id' => $batch->id,
            'product_id' => $product2->id,
            'product_code_snapshot' => 'SKU-POS-CHECK-2',
            'unit_price' => 15.00,
            'quantity_sold' => 5,
            'total_amount' => 75.00,
        ]);

        // Assert inventory decrements
        $this->assertSame(98, $product1->fresh()->current_stock);
        $this->assertSame(15, $product2->fresh()->current_stock);
    }
}
