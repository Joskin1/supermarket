<?php

namespace Tests\Feature;

use App\Actions\Users\EnsureUserAccountSafetyAction;
use App\Enums\RoleEnum;
use App\Models\Product;
use App\Models\SalesImportBatch;
use App\Models\StockEntry;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class UserAccountSafetyTest extends TestCase
{
    use RefreshDatabase;

    public function test_last_sudo_user_cannot_be_reassigned(): void
    {
        $this->seed(RoleSeeder::class);
        $sudo = $this->makeSudo();

        $this->expectException(ValidationException::class);

        app(EnsureUserAccountSafetyAction::class)->ensureRoleChangeIsSafe($sudo, RoleEnum::ADMIN->value);
    }

    public function test_user_with_uploaded_sales_batches_cannot_be_deleted(): void
    {
        $this->seed(RoleSeeder::class);

        $sudo = $this->makeSudo();
        $uploader = User::factory()->create();
        $uploader->assignRole(RoleEnum::ADMIN->value);

        SalesImportBatch::factory()->create([
            'uploaded_by' => $uploader->id,
        ]);

        $this->expectException(ValidationException::class);

        app(EnsureUserAccountSafetyAction::class)->ensureCanDelete($uploader);
    }

    public function test_user_with_stock_entry_history_cannot_be_deleted(): void
    {
        $this->seed(RoleSeeder::class);

        $user = User::factory()->create();
        StockEntry::factory()->create([
            'created_by' => $user->id,
            'product_id' => Product::factory(),
        ]);

        $this->expectException(ValidationException::class);

        app(EnsureUserAccountSafetyAction::class)->ensureCanDelete($user);
    }

    public function test_self_delete_is_blocked_for_the_last_sudo_user(): void
    {
        $sudo = $this->makeSudo();

        $this->actingAs($sudo);

        Livewire::test('pages::settings.delete-user-modal')
            ->set('password', 'password')
            ->call('deleteUser')
            ->assertHasErrors(['account']);

        $this->assertNotNull($sudo->fresh());
    }

    public function test_self_delete_is_blocked_when_the_account_has_uploaded_sales_batches(): void
    {
        $this->seed(RoleSeeder::class);

        $user = User::factory()->create();
        SalesImportBatch::factory()->create([
            'uploaded_by' => $user->id,
        ]);

        $this->actingAs($user);

        Livewire::test('pages::settings.delete-user-modal')
            ->set('password', 'password')
            ->call('deleteUser')
            ->assertHasErrors(['account']);

        $this->assertNotNull($user->fresh());
    }
}
