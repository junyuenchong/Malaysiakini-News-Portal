<?php

namespace App\Modules\Category\Http\Controllers;

use App\Modules\Category\Http\Requests\CategoryNewsRequest;
use App\Modules\Category\Services\CategoryService;
use App\Modules\News\Services\NewsService;
use App\Support\Cache\CacheKey;
use App\Support\Cache\CacheService;
use Illuminate\Http\JsonResponse;

/**
 * Category API controller.
 *
 * Thin entry point for category routes.
 * Calls service for data, builds HTTP response via CacheService.
 *
 * Routes:
 *   GET /api/categories
 *   GET /api/categories/{id}/news
 *   GET /api/menu
 */
class CategoryController
{
    public function __construct(
        private readonly CategoryService $categoryService,
        private readonly NewsService $newsService,
        private readonly CacheService $cache,
    ) {}

    /**
     * GET /api/categories
     */
    public function index(): JsonResponse
    {
        return $this->cache->jsonWithCacheHeader($this->categoryService->getAll(), CacheKey::CATEGORY_TTL);
    }

    /**
     * GET /api/categories/{id}/news
     */
    public function news(CategoryNewsRequest $request): JsonResponse
    {
        return $this->cache->jsonWithCacheHeader(
            $this->newsService->getByCategoryId(
                $request->categoryId(),
                $request->page(),
                $request->perPage(),
            ),
            CacheKey::NEWS_LIST_TTL,
        );
    }

    /**
     * GET /api/menu
     */
    public function menu(): JsonResponse
    {
        return $this->cache->jsonWithCacheHeader($this->categoryService->getMenu(), CacheKey::CATEGORY_TTL);
    }
}
