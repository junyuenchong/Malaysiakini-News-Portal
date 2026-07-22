<?php

namespace App\Modules\News\Http\Controllers;

use App\Modules\News\Http\Requests\NewsRequest;
use App\Modules\News\Services\NewsService;
use App\Support\Cache\CacheKey;
use App\Support\Cache\CacheService;
use App\Support\Http\Concerns\RespondsWithCachedJson;
use Illuminate\Http\JsonResponse;

/**
 * News API controller.
 *
 * Thin entry point for news routes.
 * Validation lives in NewsRequest.
 * Calls service for data, builds HTTP response via CacheService.
 *
 * Routes:
 *   GET /api/news
 *   GET /api/news/{id}
 */
class NewsController
{
    use RespondsWithCachedJson;

    public function __construct(
        private readonly NewsService $newsService,
        private readonly CacheService $cache,
    ) {}

    /**
     * GET /api/news
     *
     * Returns a paginated news list.
     * Query params: category, page, per_page
     */
    public function index(NewsRequest $request): JsonResponse
    {
        return $this->cachedResponse(
            $this->newsService->getList(
                $request->categorySlug(),
                $request->page(),
                $request->perPage(),
            ),
            CacheKey::NEWS_LIST_TTL,
        );
    }

    /**
     * GET /api/news/{id}
     *
     * Returns one article with full content.
     */
    public function show(NewsRequest $request): JsonResponse
    {
        return $this->cachedResponse(
            $this->newsService->getById($request->newsId()),
            CacheKey::NEWS_DETAIL_MAX_AGE,
        );
    }
}
