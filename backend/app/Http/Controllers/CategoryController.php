<?php

namespace App\Http\Controllers;

use App\Http\Helpers\CategoryHelper;
use App\Http\Helpers\NewsHelper;
use App\Http\Requests\CategoryNewsRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;

/**
 * Category API controller.
 *
 * Routes:
 *   GET /api/categories
 *   GET /api/categories/{id}/news
 *   GET /api/menu
 */
class CategoryController
{
    /**
     * GET /api/categories
     */
    public function index(): JsonResponse
    {
        return CategoryHelper::cachedList('categories:all');
    }

    /**
     * GET /api/categories/{id}/news
     */
    public function news(CategoryNewsRequest $request): JsonResponse
    {
        return NewsHelper::cachedListByCategoryId(
            $request->categoryId(),
            $request->page(),
            $request->perPage(),
        );
    }

    /**
     * GET /api/menu
     */
    public function menu(): JsonResponse
    {
        return CategoryHelper::cachedList(
            'categories:menu',
            fn (Builder $query) => $query->where('show_in_menu', true),
        );
    }
}
