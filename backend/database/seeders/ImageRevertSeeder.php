<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

/**
 * Removes cached news image files from disk.
 *
 * Rollback pair for ImageSeeder.
 */
class ImageRevertSeeder extends Seeder
{
    public function run(): void
    {
        $directory = storage_path('app/public/news');

        if (! File::isDirectory($directory)) {
            $this->command?->warn('No news image directory found — nothing to remove.');

            return;
        }

        $files = File::glob($directory.'/*.jpg');
        $count = count($files);

        foreach ($files as $file) {
            File::delete($file);
        }

        Storage::disk('public')->deleteDirectory('news');

        $this->command?->info("Removed {$count} cached image(s) from storage/app/public/news/");
    }
}
