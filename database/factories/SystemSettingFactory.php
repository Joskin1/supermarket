<?php

namespace Database\Factories;

use App\Models\SystemSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SystemSetting>
 */
class SystemSettingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'singleton_key' => SystemSetting::SINGLETON_KEY,
            'business_name' => 'Supermarket HQ',
            'business_timezone' => 'Africa/Lagos',
            'currency_code' => 'NGN',
            'low_stock_contact_email' => fake()->safeEmail(),
            'receipt_footer' => fake()->sentence(),
        ];
    }

    public function legacy(): static
    {
        return $this->state(fn (): array => [
            'singleton_key' => 'legacy-'.fake()->uuid(),
        ]);
    }
}
