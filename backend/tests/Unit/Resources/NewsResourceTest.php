<?php

namespace Tests\Unit\Resources;

use App\Http\Resources\NewsResource;
use App\Models\Category;
use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class NewsResourceTest extends TestCase
{
    public function test_list_shape_omits_content_when_column_not_selected(): void
    {
        $news = new News;
        $news->forceFill([
            'id' => 1,
            'title' => 'Headline',
            'slug' => 'headline',
            'summary' => 'Short summary',
            'image_url' => '/storage/news/1.jpg',
            'author' => 'Reporter',
            'is_featured' => false,
        ]);
        $news->published_at = Carbon::parse('2026-01-15 10:00:00');

        $payload = (new NewsResource($news))->resolve(Request::create('/'));

        $this->assertArrayNotHasKey('content', $payload);
        $this->assertSame('Headline', $payload['title']);
        $this->assertSame('2026-01-15T10:00:00+00:00', $payload['published_at']);
    }

    public function test_detail_shape_includes_content_when_column_is_present(): void
    {
        $news = new News;
        $news->forceFill([
            'id' => 2,
            'title' => 'Detail headline',
            'slug' => 'detail-headline',
            'summary' => 'Summary',
            'content' => '<p>Full body</p>',
            'image_url' => null,
            'author' => 'Editor',
            'is_featured' => true,
        ]);
        $news->published_at = Carbon::parse('2026-02-01 08:30:00');

        $payload = (new NewsResource($news))->resolve(Request::create('/'));

        $this->assertSame('<p>Full body</p>', $payload['content']);
        $this->assertTrue($payload['is_featured']);
    }

    public function test_includes_category_when_relation_is_loaded(): void
    {
        $category = new Category;
        $category->forceFill([
            'id' => 10,
            'name' => 'World',
            'slug' => 'world',
            'sort_order' => 1,
        ]);

        $news = new News;
        $news->forceFill([
            'id' => 3,
            'title' => 'World news',
            'slug' => 'world-news',
            'summary' => 'Summary',
            'author' => 'Reporter',
            'is_featured' => false,
        ]);
        $news->setRelation('category', $category);

        $payload = (new NewsResource($news))->response()->getData(true)['data'];

        $this->assertIsArray($payload['category']);
        $this->assertSame('world', $payload['category']['slug']);
    }
}
