<?php

namespace Database\Factories;

use App\Models\BarcodeLookup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BarcodeLookup>
 */
class BarcodeLookupFactory extends Factory
{
    public function definition(): array
    {
        return [
            'barcode' => fake()->unique()->numerify('#############'),
            'source' => 'openfoodfacts',
            'product_name' => fake()->words(3, true),
            'brand' => fake()->company(),
            'category_hint' => fake()->randomElement(['Beverages', 'Snacks', 'Dairy', 'Household']),
            'raw_response' => ['status' => 1, 'product' => ['product_name' => 'Test']],
            'looked_up_at' => now(),
        ];
    }
}
