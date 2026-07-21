<?php

namespace App\Http\Helpers;

use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;

/**
 * Category query + cache helper.
 *
 * Shared by CategoryController index() and menu().
 * Both routes return the same JSON shape but may use
 * different cache keys and optional query filters.
 */
class CategoryHelper
{
    // Categories change rarely, cache for 5 minutes
    private const CACHE_TTL = 300;

    /**
     * Return a cached category list.
     *
     * index() uses:
     *   cache key = categories:all
     *   scope = none
     *
     * menu() uses:
     *   cache key = categories:menu
     *   scope = show_in_menu = true
     *
     * @param  string  $cacheKey  Separate key per route
     * @param  (callable(Builder<Category>): Builder<Category>)|null  $scope  Optional query filter
     */
    public static function cachedList(string $cacheKey, ?callable $scope = null): JsonResponse
    {
        return JsonCacheHelper::respond($cacheKey, self::CACHE_TTL, function () use ($scope) {
            // Select only fields used by CategoryResource
            $query = Category::query()
                ->select(Category::API_COLUMNS)
                ->orderBy('sort_order');

            // menu() passes a filter here; index() does not
            if ($scope !== null) {
                $query = $scope($query);
            }

            return CategoryResource::collection($query->get())->response()->getData(true);
        });
    }
}
