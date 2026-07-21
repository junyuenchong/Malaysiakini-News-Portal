<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\News;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_menu_returns_visible_categories_ordered(): void
    {
        Category::factory()->create(['name' => 'Hidden', 'slug' => 'hidden', 'show_in_menu' => false, 'sort_order' => 1]);
        Category::factory()->create(['name' => 'World', 'slug' => 'world', 'show_in_menu' => true, 'sort_order' => 2]);
        Category::factory()->create(['name' => 'Business', 'slug' => 'business', 'show_in_menu' => true, 'sort_order' => 1]);

        $response = $this->getJson('/api/menu');

        $response
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.slug', 'business')
            ->assertJsonPath('data.1.slug', 'world');
    }

    public function test_categories_returns_all_categories(): void
    {
        Category::factory()->count(3)->create();

        $response = $this->getJson('/api/categories');

        $response->assertOk()->assertJsonCount(3, 'data');
    }

    public function test_category_news_returns_only_articles_for_category(): void
    {
        $world = Category::factory()->create(['slug' => 'world']);
        $sports = Category::factory()->create(['slug' => 'sports']);

        News::factory()->count(2)->create(['category_id' => $world->id]);
        News::factory()->create(['category_id' => $sports->id]);

        $response = $this->getJson('/api/categories/'.$world->id.'/news');

        $response
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure([
                'data' => [['id', 'title', 'summary', 'category']],
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            ]);
    }

    public function test_category_news_returns_404_for_missing_category(): void
    {
        $this->getJson('/api/categories/999/news')->assertNotFound();
    }
}
