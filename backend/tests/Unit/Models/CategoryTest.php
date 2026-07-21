<?php

namespace Tests\Unit\Models;

use App\Models\Category;
use App\Models\News;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unit tests for the Category model.
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
        News::factory()->count(2)->create(['category_id' => $category->id]);

        $this->assertCount(2, $category->news);
        $this->assertTrue($category->news->every(fn (News $item) => $item->category_id === $category->id));
    }
}
