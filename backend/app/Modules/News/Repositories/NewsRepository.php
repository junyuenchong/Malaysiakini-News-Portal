<?php

namespace App\Modules\News\Repositories;

use App\Modules\Category\Models\Category;
use App\Modules\News\Models\News;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * News data-access layer.
 *
 * Owns all Eloquent queries for news articles.
 * Eager-loads category here to prevent N+1 at the query level.
 */
class NewsRepository
{
    // Columns for list endpoints — excludes heavy `content` field
    private const LIST_COLUMNS = [
        'id',
        'category_id',
        'title',
        'slug',
        'summary',
        'image_url',
        'author',
        'published_at',
        'is_featured',
    ];

    // Columns for detail endpoint — includes full article body
    private const DETAIL_COLUMNS = [
        'id',
        'category_id',
        'title',
        'slug',
        'summary',
        'content',
        'image_url',
        'author',
        'published_at',
        'is_featured',
    ];

    /**
     * Paginate news articles, optionally filtered by category.
     *
     * @param  int|null  $categoryId  Filter by category, or null for all
     * @param  int  $page  Current page number
     * @param  int  $perPage  Items per page
     */
    public function paginate(?int $categoryId, int $page, int $perPage): LengthAwarePaginator
    {
        // Select list columns + eager-load category (prevents N+1)
        $query = News::query()
            ->select(self::LIST_COLUMNS)
            ->with(['category:'.implode(',', Category::API_COLUMNS)]);

        // Apply category filter when provided
        if ($categoryId !== null) {
            $query->where('category_id', $categoryId);
        }

        // Newest articles first
        return $query
            ->orderByDesc('published_at')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Return an empty paginator (same shape as a real page).
     *
     * @param  int  $page  Requested page number
     * @param  int  $perPage  Items per page
     */
    public function emptyPaginate(int $page, int $perPage): LengthAwarePaginator
    {
        // whereRaw('0 = 1') guarantees zero rows while keeping Laravel pagination
        return News::query()
            ->whereRaw('0 = 1')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Find one article with full content and eager-loaded category.
     *
     * @param  int  $id  News article primary key
     */
    public function findOrFailWithCategory(int $id): News
    {
        // Select detail columns + eager-load category (prevents N+1)
        return News::query()
            ->select(self::DETAIL_COLUMNS)
            ->with(['category:'.implode(',', Category::API_COLUMNS)])
            ->findOrFail($id);
    }
}
