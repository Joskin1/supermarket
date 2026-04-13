<?php

namespace Database\Factories;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ActivityLog>
 */
class ActivityLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'actor_id' => User::factory(),
            'event' => fake()->randomElement([
                'stock_entry.created',
                'stock_adjustment.created',
                'sales_import_batch.uploaded',
                'sales_import_batch.processed',
            ]),
            'description' => fake()->sentence(),
            'subject_type' => null,
            'subject_id' => null,
            'properties' => [
                'reference' => fake()->optional()->bothify('REF-#####'),
            ],
            'created_at' => now(),
        ];
    }
}
