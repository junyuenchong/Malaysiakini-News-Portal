<?php

namespace App\Http\Helpers;

use App\Http\Projections\NewsProjection;
use App\Models\Category;
use App\Models\News;
use Illuminate\Http\JsonResponse;

/**
 * News query + cache helper.
 *
 * Shared by NewsController index() and show().
 * Keeps DB queries, filters, and cache keys out of the controller.
 */
class NewsHelper
{
    // Cache list responses for 30 seconds
    private const LIST_CACHE_TTL = 30;

    // Browser may cache detail responses longer than app cache
    private const DETAIL_CACHE_MAX_AGE = 60;

    // List endpoint columns (no full article body)
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

    // Detail endpoint columns (includes content)
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
     * Return a paginated, cached news list.
     *
     * Category filter rules:
     *   - no slug: return all news
     *   - valid slug: filter by category_id
     *   - unknown slug: return empty page (not an error)
     */
    public static function cachedList(?string $categorySlug, int $page, int $perPage): JsonResponse
    {
        return JsonCacheHelper::respond(
            "news:list:{$categorySlug}:{$page}:{$perPage}",
            self::LIST_CACHE_TTL,
            function () use ($categorySlug, $perPage, $page) {
                $query = News::query()
                    ->select(self::LIST_COLUMNS)
                    ->with(['category:'.implode(',', Category::API_COLUMNS)]);

                if ($categorySlug !== null) {
                    // Resolve slug to id once, then filter by FK
                    $categoryId = Category::query()
                        ->where('slug', $categorySlug)
                        ->value('id');

                    // Unknown slug: return empty paginated result
                    if ($categoryId === null) {
                        return NewsProjection::collection(
                            News::query()->whereRaw('0 = 1')->paginate($perPage, ['*'], 'page', $page)
                        )->response()->getData(true);
                    }

                    $query->where('category_id', $categoryId);
                }

                $news = $query
                    ->orderByDesc('published_at')
                    ->paginate($perPage, ['*'], 'page', $page);

                return NewsProjection::collection($news)->response()->getData(true);
            },
        );
    }

    /**
     * Return one cached article with full content.
     *
     * Uses a separate cache key per article id.
     * Browser cache can be longer than app cache.
     */
    public static function cachedShow(int $id): JsonResponse
    {
        return JsonCacheHelper::respond(
            "news:show:{$id}",
            self::LIST_CACHE_TTL,
            function () use ($id) {
                $article = News::query()
                    ->select(self::DETAIL_COLUMNS)
                    ->with(['category:'.implode(',', Category::API_COLUMNS)])
                    ->findOrFail($id);

                return (new NewsProjection($article))->response()->getData(true);
            },
            self::DETAIL_CACHE_MAX_AGE,
        );
    }
}
