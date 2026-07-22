<?php

namespace Tests\Feature\Modules\Category;

use App\Modules\Category\Models\Category;
use App\Modules\News\Models\News;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Integration tests for the Category API.
 *
 * Sends real HTTP requests through Laravel's routing stack:
 * Route → Controller → Service → Cache → Repository → JSON response.
 */
class CategoryApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * GET /api/menu should return only visible categories, ordered by sort_order.
     */
    public function test_menu_returns_visible_categories_ordered(): void
    {
        // Hidden category should be excluded from menu
        Category::factory()->create(['name' => 'Hidden', 'slug' => 'hidden', 'show_in_menu' => false, 'sort_order' => 1]);
        Category::factory()->create(['name' => 'World', 'slug' => 'world', 'show_in_menu' => true, 'sort_order' => 2]);
        Category::factory()->create(['name' => 'Business', 'slug' => 'business', 'show_in_menu' => true, 'sort_order' => 1]);

        $response = $this->getJson('/api/menu');

        $response
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.slug', 'business') // sort_order 1 first
            ->assertJsonPath('data.1.slug', 'world');
    }

    /**
     * GET /api/categories should return every category regardless of menu visibility.
     */
    public function test_categories_returns_all_categories(): void
    {
        Category::factory()->count(3)->create();

        $response = $this->getJson('/api/categories');

        $response->assertOk()->assertJsonCount(3, 'data');
    }

    /**
     * GET /api/categories/{id}/news should return paginated articles for one category only.
     */
    public function test_category_news_returns_only_articles_for_category(): void
    {
        $world = Category::factory()->create(['slug' => 'world']);
        $sports = Category::factory()->create(['slug' => 'sports']);

        News::factory()->count(2)->create(['category_id' => $world->id]);
        News::factory()->create(['category_id' => $sports->id]); // should not appear

        $response = $this->getJson('/api/categories/'.$world->id.'/news');

        $response
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure([
                'data' => [['id', 'title', 'summary', 'category']],
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            ]);
    }

    /**
     * GET /api/categories/{id}/news should return 404 when category id does not exist.
     */
    public function test_category_news_returns_404_for_missing_category(): void
    {
        $this->getJson('/api/categories/999/news')->assertNotFound();
    }
}
