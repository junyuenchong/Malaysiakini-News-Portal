<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\News;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Integration (Feature) tests for the News API.
 *
 * Sends real HTTP requests through Laravel's routing stack:
 * Route → Controller → Model → Database → JSON response.
 */
class NewsApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * GET /api/news should return a paginated list with the expected JSON shape.
     */
    public function test_news_list_returns_paginated_articles(): void
    {
        $category = Category::factory()->create(['slug' => 'politics']);
        News::factory()->count(3)->create(['category_id' => $category->id]);

        $response = $this->getJson('/api/news');

        $response
            ->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [['id', 'title', 'slug', 'summary', 'category']],
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            ])
            ->assertHeader('Cache-Control');
    }

    /**
     * GET /api/news?category=politics should only return articles from that category.
     */
    public function test_news_list_filters_by_category_slug(): void
    {
        $politics = Category::factory()->create(['slug' => 'politics']);
        $sports = Category::factory()->create(['slug' => 'sports']);

        News::factory()->count(2)->create(['category_id' => $politics->id]);
        News::factory()->create(['category_id' => $sports->id]);

        $response = $this->getJson('/api/news?category=politics');

        $response->assertOk()->assertJsonCount(2, 'data');
    }

    /**
     * GET /api/news/{id} should return one article including the full content field.
     */
    public function test_news_detail_returns_single_article_with_content(): void
    {
        $news = News::factory()->create([
            'title' => 'Test headline',
            'content' => '<p>Full article body</p>',
        ]);

        $response = $this->getJson('/api/news/'.$news->id);

        $response
            ->assertOk()
            ->assertJsonPath('data.title', 'Test headline')
            ->assertJsonPath('data.content', '<p>Full article body</p>')
            ->assertJsonPath('data.category.slug', $news->category->slug);
    }

    /**
     * GET /api/news/{id} should return 404 when the article does not exist.
     */
    public function test_news_detail_returns_404_for_missing_article(): void
    {
        $this->getJson('/api/news/999')->assertNotFound();
    }
}
