<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StockAdjustment>
 */
class StockAdjustmentFactory extends Factory
{
    public function definition(): array
    {
        $previousStock = fake()->numberBetween(1, 40);
        $quantityChange = fake()->numberBetween(-10, 10);
        $newStock = max($previousStock + $quantityChange, 0);

        return [
            'product_id' => Product::factory(),
            'quantity_change' => $quantityChange,
            'previous_stock' => $previousStock,
            'new_stock' => $newStock,
            'counted_stock' => null,
            'reason' => fake()->randomElement([
                'Damaged items removed',
                'Physical stock count correction',
                'Shortage after shelf count',
                'Recovered missing stock',
            ]),
            'reference' => fake()->optional()->bothify('ADJ-#####'),
            'note' => fake()->optional()->sentence(),
            'adjustment_date' => fake()->date(),
            'adjusted_by' => User::factory(),
        ];
    }
}
