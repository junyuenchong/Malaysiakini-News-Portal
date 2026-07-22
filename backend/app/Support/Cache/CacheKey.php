<?php

namespace App\Support\Cache;

/**
 * Central cache-key builder and TTL constants.
 *
 * Keeps key naming consistent so cache invalidation
 * and debugging stay predictable. Change a key in one place only.
 *
 * Usage:
 *   CacheKey::categoriesAll()
 *   CacheKey::CATEGORY_TTL
 */
class CacheKey
{
    // News list app-cache lifetime (seconds)
    public const NEWS_LIST_TTL = 30;

    // News detail browser Cache-Control max-age (seconds)
    public const NEWS_DETAIL_MAX_AGE = 60;

    // Category list app-cache + browser cache lifetime (seconds)
    public const CATEGORY_TTL = 300;

    /**
     * GET /api/categories — all categories.
     */
    public static function categoriesAll(): string
    {
        return 'categories:all';
    }

    /**
     * GET /api/menu — menu-visible categories only.
     */
    public static function categoriesMenu(): string
    {
        return 'categories:menu';
    }

    /**
     * GET /api/news — paginated list with optional category filter.
     *
     * @param  string|null  $categorySlug  Category slug or null for all
     * @param  int  $page  Current page number
     * @param  int  $perPage  Items per page
     */
    public static function newsList(?string $categorySlug, int $page, int $perPage): string
    {
        return "news:list:{$categorySlug}:{$page}:{$perPage}";
    }

    /**
     * GET /api/categories/{id}/news — paginated news for one category.
     *
     * @param  int  $categoryId  Category primary key
     * @param  int  $page  Current page number
     * @param  int  $perPage  Items per page
     */
    public static function newsByCategory(int $categoryId, int $page, int $perPage): string
    {
        return "news:category:{$categoryId}:{$page}:{$perPage}";
    }

    /**
     * GET /api/news/{id} — single article detail.
     *
     * @param  int  $id  News article primary key
     */
    public static function newsShow(int $id): string
    {
        return "news:show:{$id}";
    }
}
