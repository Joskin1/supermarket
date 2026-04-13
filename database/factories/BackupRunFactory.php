<?php

namespace Database\Factories;

use App\Models\BackupRun;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<BackupRun>
 */
class BackupRunFactory extends Factory
{
    public function definition(): array
    {
        $fileName = Str::lower(fake()->bothify('backup-####')).'.json';

        return [
            'backup_code' => 'BKP-'.Str::upper(fake()->bothify('??##??')),
            'disk' => 'local',
            'file_name' => $fileName,
            'file_path' => 'backups/'.$fileName,
            'status' => 'completed',
            'file_size_bytes' => fake()->numberBetween(1000, 50000),
            'checksum' => fake()->sha256(),
            'note' => null,
            'created_by' => User::factory(),
            'started_at' => now()->subMinute(),
            'completed_at' => now(),
        ];
    }
}
