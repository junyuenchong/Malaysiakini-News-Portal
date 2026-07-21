<?php

namespace App\Http\Projections;

use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * News API projection.
 *
 * Defines the JSON fields sent to the frontend.
 * Used by GET /api/news and GET /api/news/{id}.
 *
 * @mixin News
 */
class NewsProjection extends JsonResource
{
    /**
     * Map a News model to API JSON.
     *
     * List responses omit content.
     * Detail responses include content when the column was selected.
     *
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
            // Only present when detail query selected the content column
            'content' => $this->when(array_key_exists('content', $news->getAttributes()), $news->content),
            'image_url' => $news->image_url,
            'author' => $news->author,
            'published_at' => $news->published_at?->toIso8601String(),
            'is_featured' => $news->is_featured,
            // Nested category; must be eager loaded in NewsHelper
            'category' => new CategoryProjection($this->whenLoaded('category')),
        ];
    }
}
