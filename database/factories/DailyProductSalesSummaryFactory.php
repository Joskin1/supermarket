<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\DailyProductSalesSummary;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DailyProductSalesSummary>
 */
class DailyProductSalesSummaryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'sales_date' => fake()->date(),
            'product_id' => Product::factory(),
            'product_code_snapshot' => 'SKU-'.fake()->unique()->bothify('??-####'),
            'product_name_snapshot' => fake()->words(3, true),
            'category_id' => Category::factory(),
            'category_snapshot' => fake()->word(),
            'total_quantity_sold' => fake()->numberBetween(1, 100),
            'total_sales_amount' => fake()->randomFloat(2, 100, 50000),
            'transactions_count' => fake()->numberBetween(1, 20),
        ];
    }
}
