<?php

namespace Database\Seeders;

use App\Modules\News\Models\News;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * Downloads news photos and saves them to storage.
 * Run after NewsSeeder: php artisan db:seed --class=ImageSeeder
 */
class ImageSeeder extends Seeder
{
    private const DISK = 'public';

    private const DIRECTORY = 'news';

    private const WIDTH = 800;

    private const HEIGHT = 450;

    public function run(): void
    {
        $total = News::query()->count();
        $this->command->info("Downloading {$total} real news photos...");

        News::query()
            ->with('category')
            ->orderBy('id')
            ->each(function (News $news): void {
                $this->cacheAndUpdate($news);
                usleep(150_000); // pause between downloads
            });

        $this->command->info('News images saved to storage/app/public/news/');
    }

    /** Download image if missing and set image_url on the news row. */
    private function cacheAndUpdate(News $news): void
    {
        $filename = $news->id.'.jpg';
        $relativePath = self::DIRECTORY.'/'.$filename;

        if (! Storage::disk(self::DISK)->exists($relativePath)) {
            $this->downloadImage($news, $relativePath);
        }

        if (Storage::disk(self::DISK)->exists($relativePath)) {
            $news->updateQuietly([
                'image_url' => url(Storage::disk(self::DISK)->url($relativePath)),
            ]);
        }
    }

    /** Remote photo URL for seeding. */
    private function photoUrl(News $news): string
    {
        $seed = $news->id.'-'.$news->slug;

        return sprintf(
            'https://picsum.photos/seed/%s/%d/%d',
            urlencode($seed),
            self::WIDTH,
            self::HEIGHT,
        );
    }

    /** Download from picsum and save to storage. */
    private function downloadImage(News $news, string $relativePath): void
    {
        Storage::disk(self::DISK)->makeDirectory(self::DIRECTORY);

        $response = Http::timeout(30)
            ->withOptions(['verify' => false, 'allow_redirects' => true])
            ->get($this->photoUrl($news));

        // Skip tiny or invalid responses
        if ($response->successful() && strlen($response->body()) > 1000) {
            Storage::disk(self::DISK)->put($relativePath, $response->body());
        }
    }
}
