<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\SalesImportBatch;
use App\Models\SalesRecord;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SalesRecord>
 */
class SalesRecordFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $unitPrice = fake()->randomFloat(2, 100, 10000);
        $quantity = fake()->numberBetween(1, 20);

        return [
            'batch_id' => SalesImportBatch::factory(),
            'product_id' => Product::factory(),
            'product_code_snapshot' => 'SKU-'.fake()->unique()->bothify('??-####'),
            'category_snapshot' => fake()->word(),
            'product_name_snapshot' => fake()->words(3, true),
            'unit_price' => $unitPrice,
            'quantity_sold' => $quantity,
            'total_amount' => round($unitPrice * $quantity, 2),
            'sales_date' => now()->toDateString(),
            'note' => null,
            'created_by' => User::factory(),
        ];
    }

    /**
     * Create a record linked to a specific product, auto-filling snapshot fields from it.
     */
    public function forProduct(Product $product): static
    {
        return $this->state(function () use ($product) {
            $quantity = fake()->numberBetween(1, 10);

            return [
                'product_id' => $product->id,
                'product_code_snapshot' => $product->sku,
                'product_name_snapshot' => $product->name,
                'category_snapshot' => $product->category?->name,
                'unit_price' => $product->selling_price,
                'quantity_sold' => $quantity,
                'total_amount' => round((float) $product->selling_price * $quantity, 2),
            ];
        });
    }

    public function onDate(string $date): static
    {
        return $this->state(fn () => [
            'sales_date' => $date,
        ]);
    }
}
