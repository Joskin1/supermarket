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
            'business_name' => 'Supermarket HQ',
            'business_timezone' => 'Africa/Lagos',
            'currency_code' => 'NGN',
            'low_stock_contact_email' => fake()->safeEmail(),
            'receipt_footer' => fake()->sentence(),
        ];
    }
}
