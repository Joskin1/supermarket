<?php

namespace Database\Factories;

use App\Models\SalesImportBatch;
use App\Models\SalesImportFailure;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SalesImportFailure>
 */
class SalesImportFailureFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'batch_id' => SalesImportBatch::factory(),
            'row_number' => fake()->numberBetween(2, 200),
            'raw_row' => [
                'date' => fake()->date('Y-m-d'),
                'product_code' => fake()->bothify('SKU-??-####'),
                'quantity_sold' => fake()->numberBetween(0, 5),
            ],
            'error_messages' => [
                fake()->randomElement([
                    'The product code does not match any existing product.',
                    'The quantity sold field must be at least 1.',
                    'The quantity sold exceeds the current stock for this product.',
                ]),
            ],
            'product_code' => fake()->optional()->bothify('SKU-??-####'),
            'product_name' => fake()->optional()->words(3, true),
            'sales_date' => fake()->optional()->date('Y-m-d'),
        ];
    }
}
