<?php

namespace App\Http\Resources;

use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * News API resource.
 *
 * Used by GET /api/news, GET /api/news/{id}, and GET /api/categories/{id}/news.
 *
 * @mixin News
 */
class NewsResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var News $news */
        $news = $this->resource;

        return [
            'id' => $news->id,
            'title' => $news->title,
            'slug' => $news->slug,
            'summary' => $news->summary,
            'content' => $this->when(array_key_exists('content', $news->getAttributes()), $news->content),
            'image_url' => $news->image_url,
            'author' => $news->author,
            'published_at' => $news->published_at?->toIso8601String(),
            'is_featured' => $news->is_featured,
            'category' => new CategoryResource($this->whenLoaded('category')),
        ];
    }
}
