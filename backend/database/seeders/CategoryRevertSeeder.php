<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\News;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

/**
 * Deletes all categories from the database.
 *
 * Rollback pair for CategorySeeder. News rows must be removed first (FK constraint).
 */
class CategoryRevertSeeder extends Seeder
{
    public function run(): void
    {
        if (News::query()->exists()) {
            $this->command?->error('Cannot remove categories while news articles exist.');
            $this->command?->line('Run NewsRevertSeeder first: php artisan seed:rollback news');

            return;
        }

        Schema::disableForeignKeyConstraints();

        $count = Category::query()->count();
        Category::query()->truncate();

        Schema::enableForeignKeyConstraints();

        $this->command?->info("Removed {$count} categor(ies).");
    }
}
