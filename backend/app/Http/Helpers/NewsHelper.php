<?php

namespace App\Http\Helpers;

use App\Http\Resources\NewsResource;
use App\Models\Category;
use App\Models\News;
use Illuminate\Http\JsonResponse;

/**
 * News query + cache helper.
 *
 * Shared by NewsController and CategoryController news().
 * Controllers stay thin — this class owns DB queries, eager loading,
 * pagination, and response caching.
 *
 * Routes:
 *   GET /api/news                  → cachedList()
 *   GET /api/news/{id}             → cachedShow()
 *   GET /api/categories/{id}/news  → cachedListByCategoryId()
 */
class NewsHelper
{
    // List/show responses cached for 30 seconds
    private const LIST_CACHE_TTL = 30;

    // Browser Cache-Control max-age for single-article responses
    private const DETAIL_CACHE_MAX_AGE = 60;

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
     * GET /api/news — cached paginated news list.
     *
     * Optional ?category=slug filter:
     *   1. Resolve slug → category id
     *   2. Unknown slug → empty page (still 200)
     *   3. Known slug / no filter → paginatedList()
     *
     * Cache key includes slug, page, and per_page so each
     * combination is stored separately.
     *
     * @param  string|null  $categorySlug  Optional category slug from query string
     * @param  int  $page  Current page number
     * @param  int  $perPage  Items per page
     */
    public static function cachedList(?string $categorySlug, int $page, int $perPage): JsonResponse
    {
        return JsonCacheHelper::respond(
            "news:list:{$categorySlug}:{$page}:{$perPage}",
            self::LIST_CACHE_TTL,
            function () use ($categorySlug, $perPage, $page) {
                $categoryId = null;

                // Resolve slug to id before querying news (avoids join)
                if ($categorySlug !== null) {
                    $categoryId = Category::query()
                        ->where('slug', $categorySlug)
                        ->value('id');

                    // Unknown slug — return empty pagination, not 404
                    if ($categoryId === null) {
                        return self::emptyPage($page, $perPage);
                    }
                }

                return self::paginatedList($categoryId, $page, $perPage);
            },
        );
    }

    /**
     * GET /api/categories/{id}/news — cached news for one category.
     *
     * Verifies the category exists (404 if missing), then reuses
     * the same paginatedList() query as the main news list.
     *
     * @param  int  $categoryId  Category primary key from route
     * @param  int  $page  Current page number
     * @param  int  $perPage  Items per page
     */
    public static function cachedListByCategoryId(int $categoryId, int $page, int $perPage): JsonResponse
    {
        // Fail fast with 404 when category id is invalid
        Category::query()->findOrFail($categoryId);

        return JsonCacheHelper::respond(
            "news:category:{$categoryId}:{$page}:{$perPage}",
            self::LIST_CACHE_TTL,
            fn () => self::paginatedList($categoryId, $page, $perPage),
        );
    }

    /**
     * GET /api/news/{id} — cached single article with full content.
     *
     * Eager-loads category so NewsResource can include it
     * without an N+1 query. Uses a longer browser max-age
     * because article detail changes less often than lists.
     *
     * @param  int  $id  News article primary key
     */
    public static function cachedShow(int $id): JsonResponse
    {
        return JsonCacheHelper::respond(
            "news:show:{$id}",
            self::LIST_CACHE_TTL,
            function () use ($id) {
                // Select detail columns + eager-load category (prevents N+1)
                $article = News::query()
                    ->select(self::DETAIL_COLUMNS)
                    ->with(['category:'.implode(',', Category::API_COLUMNS)])
                    ->findOrFail($id);

                // Convert Resource → array so Cache::remember can store it
                return (new NewsResource($article))->response()->getData(true);
            },
            self::DETAIL_CACHE_MAX_AGE,
        );
    }

    /**
     * Build a paginated news list (shared by list endpoints).
     *
     * - Selects LIST_COLUMNS only (no content)
     * - Eager-loads category via with() to avoid N+1
     * - Optionally filters by category_id
     * - Orders newest first
     *
     * @param  int|null  $categoryId  Filter by category, or null for all
     * @param  int  $page  Current page number
     * @param  int  $perPage  Items per page
     * @return array<string, mixed>  JSON-ready paginated payload
     */
    private static function paginatedList(?int $categoryId, int $page, int $perPage): array
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
        $news = $query
            ->orderByDesc('published_at')
            ->paginate($perPage, ['*'], 'page', $page);

        // Convert Resource collection → array for caching
        return NewsResource::collection($news)->response()->getData(true);
    }

    /**
     * Return an empty paginated page (same JSON shape as a real list).
     *
     * Used when ?category=slug does not match any category,
     * so the client still gets meta/links without a 404.
     *
     * @param  int  $page  Requested page number
     * @param  int  $perPage  Items per page
     * @return array<string, mixed>  Empty paginated payload
     */
    private static function emptyPage(int $page, int $perPage): array
    {
        // whereRaw('0 = 1') guarantees zero rows while keeping Laravel pagination
        return NewsResource::collection(
            News::query()->whereRaw('0 = 1')->paginate($perPage, ['*'], 'page', $page)
        )->response()->getData(true);
    }
}
