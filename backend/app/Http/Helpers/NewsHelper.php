<?php

namespace App\Http\Helpers;

use App\Http\Resources\NewsResource;
use App\Models\Category;
use App\Models\News;
use Illuminate\Http\JsonResponse;

/**
 * News query + cache helper.
 */
class NewsHelper
{
    private const LIST_CACHE_TTL = 30;

    private const DETAIL_CACHE_MAX_AGE = 60;

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

    public static function cachedList(?string $categorySlug, int $page, int $perPage): JsonResponse
    {
        return JsonCacheHelper::respond(
            "news:list:{$categorySlug}:{$page}:{$perPage}",
            self::LIST_CACHE_TTL,
            function () use ($categorySlug, $perPage, $page) {
                $categoryId = null;

                if ($categorySlug !== null) {
                    $categoryId = Category::query()
                        ->where('slug', $categorySlug)
                        ->value('id');

                    if ($categoryId === null) {
                        return self::emptyPage($page, $perPage);
                    }
                }

                return self::paginatedList($categoryId, $page, $perPage);
            },
        );
    }

    public static function cachedListByCategoryId(int $categoryId, int $page, int $perPage): JsonResponse
    {
        Category::query()->findOrFail($categoryId);

        return JsonCacheHelper::respond(
            "news:category:{$categoryId}:{$page}:{$perPage}",
            self::LIST_CACHE_TTL,
            fn () => self::paginatedList($categoryId, $page, $perPage),
        );
    }

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

                return (new NewsResource($article))->response()->getData(true);
            },
            self::DETAIL_CACHE_MAX_AGE,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private static function paginatedList(?int $categoryId, int $page, int $perPage): array
    {
        $query = News::query()
            ->select(self::LIST_COLUMNS)
            ->with(['category:'.implode(',', Category::API_COLUMNS)]);

        if ($categoryId !== null) {
            $query->where('category_id', $categoryId);
        }

        $news = $query
            ->orderByDesc('published_at')
            ->paginate($perPage, ['*'], 'page', $page);

        return NewsResource::collection($news)->response()->getData(true);
    }

    /**
     * @return array<string, mixed>
     */
    private static function emptyPage(int $page, int $perPage): array
    {
        return NewsResource::collection(
            News::query()->whereRaw('0 = 1')->paginate($perPage, ['*'], 'page', $page)
        )->response()->getData(true);
    }
}
