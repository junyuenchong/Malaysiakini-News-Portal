<?php

namespace App\Http\Projections;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Category API projection.
 *
 * Defines the JSON fields sent to the frontend.
 * Used by GET /api/categories and GET /api/menu.
 *
 * @mixin Category
 */
class CategoryProjection extends JsonResource
{
    /**
     * Map a Category model to API JSON.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,           // used for filters and URLs
            'sort_order' => $this->sort_order, // menu display order
        ];
    }
}
