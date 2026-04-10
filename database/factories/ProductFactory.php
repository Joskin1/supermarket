<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $catalogEntry = fake()->randomElement([
            ['group' => 'Perfume', 'brand' => 'Zara', 'name' => 'Gold Perfume', 'variant' => '50ml', 'unit' => 'bottle'],
            ['group' => 'Body Spray', 'brand' => 'Classic', 'name' => 'Fresh Body Mist', 'variant' => '100ml', 'unit' => 'bottle'],
            ['group' => 'Soap', 'brand' => 'Dettol', 'name' => 'Original Bathing Soap', 'variant' => '175g', 'unit' => 'pcs'],
            ['group' => 'Toothpaste', 'brand' => 'Colgate', 'name' => 'MaxFresh Toothpaste', 'variant' => '120g', 'unit' => 'pcs'],
            ['group' => 'Rice', 'brand' => 'Mama Gold', 'name' => 'Premium Parboiled Rice', 'variant' => '5kg', 'unit' => 'bag'],
            ['group' => 'Soft Drink', 'brand' => 'Coca-Cola', 'name' => 'Classic Soft Drink', 'variant' => '50cl', 'unit' => 'bottle'],
            ['group' => 'Detergent', 'brand' => 'Ariel', 'name' => 'Ultra Clean Detergent', 'variant' => '850g', 'unit' => 'pack'],
        ]);
        $purchasePrice = fake()->randomFloat(2, 300, 25000);
        $sellingPrice = round($purchasePrice * fake()->randomFloat(2, 1.05, 1.35), 2);
        $name = trim($catalogEntry['brand'].' '.$catalogEntry['name']);
        $variant = $catalogEntry['variant'];

        return [
            'category_id' => Category::factory(),
            'product_group' => $catalogEntry['group'],
            'name' => $name,
            'slug' => Str::slug(trim($name.' '.$variant)),
            'sku' => Str::upper(fake()->unique()->bothify('SKU-??-####')),
            'brand' => $catalogEntry['brand'],
            'variant' => $variant,
            'description' => fake()->sentence(),
            'purchase_price' => $purchasePrice,
            'selling_price' => $sellingPrice,
            'current_stock' => 0,
            'reorder_level' => fake()->numberBetween(1, 10),
            'unit_of_measure' => $catalogEntry['unit'],
            'is_active' => true,
        ];
    }
}
