<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\StockEntry;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StockEntry>
 */
class StockEntryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $unitCostPrice = fake()->randomFloat(2, 300, 25000);
        $unitSellingPrice = round($unitCostPrice * fake()->randomFloat(2, 1.05, 1.35), 2);

        return [
            'product_id' => Product::factory(),
            'quantity_added' => fake()->numberBetween(1, 25),
            'unit_cost_price' => $unitCostPrice,
            'unit_selling_price' => $unitSellingPrice,
            'stock_date' => fake()->date(),
            'reference' => fake()->optional()->bothify('REF-#####'),
            'note' => fake()->optional()->sentence(),
            'created_by' => User::factory(),
        ];
    }
}
