<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class InventoryDevelopmentSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ApplicationDemoSeeder::class,
        ]);
    }
}
