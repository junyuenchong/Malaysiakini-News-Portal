<?php

namespace Database\Seeders;

use App\Modules\News\Models\News;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

/**
 * Deletes all news articles from the database.
 *
 * Rollback pair for NewsSeeder. Run ImageRevertSeeder first if you also want files removed.
 */
class NewsRevertSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();

        $count = News::query()->count();
        News::query()->truncate();

        Schema::enableForeignKeyConstraints();

        $this->command?->info("Removed {$count} news article(s).");
    }
}
