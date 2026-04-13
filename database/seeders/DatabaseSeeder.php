<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (app()->environment('local')) {
            $this->call([
                ApplicationDemoSeeder::class,
            ]);

            return;
        }

        $this->call([
            RoleSeeder::class,
        ]);
    }
}
