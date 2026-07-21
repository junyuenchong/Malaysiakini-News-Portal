<?php

namespace App\Http\Controllers;

use App\Http\Helpers\CategoryHelper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;

/**
 * Category API controller.
 *
 * Thin entry point for category routes.
 * Query and cache logic lives in CategoryHelper.
 *
 * Routes:
 *   GET /api/categories
 *   GET /api/menu
 */
class CategoryController
{
    /**
     * GET /api/categories
     *
     * Returns all categories ordered by sort_order.
     */
    public function index(): JsonResponse
    {
        // No filter — return every category
        return CategoryHelper::cachedList('categories:all');
    }

    /**
     * GET /api/menu
     *
     * Returns only categories shown in the navigation menu.
     */
    public function menu(): JsonResponse
    {
        return CategoryHelper::cachedList(
            'categories:menu',
            // Keep only rows flagged for the menu
            fn (Builder $query) => $query->where('show_in_menu', true),
        );
    }
}
