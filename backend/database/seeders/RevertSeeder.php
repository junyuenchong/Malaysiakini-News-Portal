<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Rolls back all portal seed data in reverse order of DatabaseSeeder.
 *
 * Order: images → news → categories
 */
class RevertSeeder extends Seeder
{
    public function run(): void
    {
        $this->command?->info('Rolling back all seeded data...');

        $this->call([
            ImageRevertSeeder::class,
            NewsRevertSeeder::class,
            CategoryRevertSeeder::class,
        ]);

        $this->command?->info('Seeder rollback complete.');
    }
}
