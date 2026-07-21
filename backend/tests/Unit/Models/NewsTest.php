<?php

namespace Tests\Unit\Models;

use App\Models\Category;
use App\Models\News;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Unit tests for the News model.
 *
 * Focuses on casts and relationships — not API behaviour.
 */
class NewsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * published_at should be cast to Carbon so we can format dates in resources.
     */
    public function test_published_at_is_cast_to_carbon(): void
    {
        $news = News::factory()->create([
            'published_at' => '2026-03-10 12:00:00',
        ]);

        $this->assertInstanceOf(Carbon::class, $news->published_at);
        $this->assertSame('2026-03-10 12:00:00', $news->published_at->format('Y-m-d H:i:s'));
    }

    /**
     * is_featured should be a real boolean, not a string "0"/"1".
     */
    public function test_is_featured_is_cast_to_boolean(): void
    {
        $news = News::factory()->create(['is_featured' => 1]);

        $this->assertIsBool($news->is_featured);
        $this->assertTrue($news->is_featured);
    }

    /**
     * Every news article belongs to exactly one category.
     */
    public function test_belongs_to_category(): void
    {
        $category = Category::factory()->create(['slug' => 'sports']);
        $news = News::factory()->create(['category_id' => $category->id]);

        $this->assertTrue($news->category->is($category));
    }
}
