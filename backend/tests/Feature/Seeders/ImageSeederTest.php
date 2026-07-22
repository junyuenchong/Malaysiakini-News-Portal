<?php

namespace Tests\Feature\Seeders;

use App\Modules\News\Models\News;
use Database\Seeders\ImageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Integration tests for ImageSeeder.
 *
 * Verifies image download, skip-on-exists, and failed-download behaviour
 * using faked HTTP and storage — no real network calls.
 */
class ImageSeederTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Use a fake public disk so tests never touch real storage.
     */
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    /**
     * ImageSeeder should download images and set local storage URLs.
     */
    public function test_image_seeder_downloads_image_and_sets_local_url(): void
    {
        // Fake a valid image response (> 1000 bytes)
        Http::fake([
            'picsum.photos/*' => Http::response(str_repeat('x', 1500), 200),
        ]);

        $news = News::factory()->create(['image_url' => null]);

        $this->seed(ImageSeeder::class);

        Storage::disk('public')->assertExists('news/'.$news->id.'.jpg');
        $news->refresh();
        $this->assertStringContainsString('/storage/news/'.$news->id.'.jpg', $news->image_url);
    }

    /**
     * ImageSeeder should skip download when file already exists.
     */
    public function test_image_seeder_skips_download_when_file_already_exists(): void
    {
        $news = News::factory()->create(['image_url' => null]);
        Storage::disk('public')->put('news/'.$news->id.'.jpg', 'existing-image');

        Http::fake();

        $this->seed(ImageSeeder::class);

        // No HTTP request should be made
        Http::assertNothingSent();
        $news->refresh();
        $this->assertStringContainsString('/storage/news/'.$news->id.'.jpg', $news->image_url);
    }

    /**
     * ImageSeeder should not save invalid downloads.
     */
    public function test_image_seeder_does_not_save_on_failed_download(): void
    {
        // Response too small — seeder should reject it
        Http::fake([
            'picsum.photos/*' => Http::response('too small', 200),
        ]);

        $news = News::factory()->create(['image_url' => null]);

        $this->seed(ImageSeeder::class);

        Storage::disk('public')->assertMissing('news/'.$news->id.'.jpg');
        $news->refresh();
        $this->assertNull($news->image_url);
    }
}
