<?php

namespace Tests\Unit\Modules\Category\Models;

use App\Modules\Category\Models\Category;
use App\Modules\News\Models\News;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unit tests for App\Modules\Category\Models\Category.
 *
 * Focuses on casts and relationships — not API behaviour.
 */
class CategoryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * show_in_menu should be a real boolean for menu filtering.
     */
    public function test_show_in_menu_is_cast_to_boolean(): void
    {
        // Store 0 in DB — Laravel should cast it to false
        $category = Category::factory()->create(['show_in_menu' => 0]);

        $this->assertIsBool($category->show_in_menu);
        $this->assertFalse($category->show_in_menu);
    }

    /**
     * A category can have many news articles.
     */
    public function test_has_many_news_articles(): void
    {
        $category = Category::factory()->create();

        // Two articles linked to the same category
        News::factory()->count(2)->create(['category_id' => $category->id]);

        $this->assertCount(2, $category->news);
        $this->assertTrue($category->news->every(fn (News $item) => $item->category_id === $category->id));
    }
}
