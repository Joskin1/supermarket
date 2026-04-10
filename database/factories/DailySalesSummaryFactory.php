<?php

namespace Database\Factories;

use App\Models\DailySalesSummary;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DailySalesSummary>
 */
class DailySalesSummaryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'sales_date' => fake()->unique()->date(),
            'total_transactions_count' => fake()->numberBetween(1, 100),
            'total_quantity_sold' => fake()->numberBetween(10, 500),
            'total_sales_amount' => fake()->randomFloat(2, 1000, 500000),
            'batches_count' => fake()->numberBetween(1, 5),
        ];
    }
}
