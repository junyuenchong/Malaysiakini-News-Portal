<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Integration (Feature) tests for the Category API.
 *
 * Sends real HTTP requests through Laravel's routing stack.
 */
class CategoryApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * GET /api/menu should return only visible categories, sorted by sort_order.
     */
    public function test_menu_returns_visible_categories_ordered(): void
    {
        Category::factory()->create(['name' => 'Hidden', 'slug' => 'hidden', 'show_in_menu' => false, 'sort_order' => 1]);
        Category::factory()->create(['name' => 'Politik', 'slug' => 'politics', 'show_in_menu' => true, 'sort_order' => 2]);
        Category::factory()->create(['name' => 'Ekonomi', 'slug' => 'economy', 'show_in_menu' => true, 'sort_order' => 1]);

        $response = $this->getJson('/api/menu');

        $response
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.slug', 'economy')
            ->assertJsonPath('data.1.slug', 'politics');
    }

    /**
     * GET /api/categories should return every category (including hidden ones).
     */
    public function test_categories_returns_all_categories(): void
    {
        Category::factory()->count(3)->create();

        $response = $this->getJson('/api/categories');

        $response->assertOk()->assertJsonCount(3, 'data');
    }
}
