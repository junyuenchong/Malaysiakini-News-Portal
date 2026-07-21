<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * Execution order matters: categories must exist before news seeding.
     */
    public function run(): void
    {
        $this->call([
            CategorySeeder::class,
            NewsSeeder::class,
            ImageSeeder::class,
        ]);
    }
}
