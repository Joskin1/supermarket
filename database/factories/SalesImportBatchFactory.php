<?php

namespace Database\Factories;

use App\Enums\SalesImportBatchStatus;
use App\Models\SalesImportBatch;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<SalesImportBatch>
 */
class SalesImportBatchFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'batch_code' => 'BATCH-'.Str::upper(fake()->bothify('??-####')),
            'file_name' => 'daily-sales-'.now()->toDateString().'.csv',
            'file_path' => 'sales-imports/'.fake()->uuid().'.csv',
            'original_file_name' => 'daily-sales.csv',
            'file_hash' => fake()->sha256(),
            'uploaded_by' => User::factory(),
            'status' => SalesImportBatchStatus::PROCESSED,
            'sales_date_from' => now()->toDateString(),
            'sales_date_to' => now()->toDateString(),
            'total_rows' => 0,
            'successful_rows' => 0,
            'failed_rows' => 0,
            'total_quantity_sold' => 0,
            'total_sales_amount' => 0,
            'notes' => null,
            'processed_at' => now(),
        ];
    }

    public function processed(): static
    {
        return $this->state(fn () => [
            'status' => SalesImportBatchStatus::PROCESSED,
            'processed_at' => now(),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn () => [
            'status' => SalesImportBatchStatus::FAILED,
            'processed_at' => now(),
        ]);
    }
}
