<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\DailyCategorySalesSummary;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DailyCategorySalesSummary>
 */
class DailyCategorySalesSummaryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'sales_date' => fake()->date(),
            'category_id' => Category::factory(),
            'category_snapshot' => fake()->word(),
            'total_quantity_sold' => fake()->numberBetween(1, 100),
            'total_sales_amount' => fake()->randomFloat(2, 100, 50000),
            'transactions_count' => fake()->numberBetween(1, 20),
        ];
    }
}
