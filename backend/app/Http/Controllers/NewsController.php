<?php

namespace App\Http\Controllers;

use App\Http\Helpers\NewsHelper;
use App\Http\Requests\NewsRequest;
use Illuminate\Http\JsonResponse;

/**
 * News API controller.
 *
 * Thin entry point for news routes.
 * Validation lives in NewsRequest.
 * Query and cache logic lives in NewsHelper.
 *
 * Routes:
 *   GET /api/news
 *   GET /api/news/{id}
 */
class NewsController
{
    /**
     * GET /api/news
     *
     * Returns a paginated news list.
     * Query params: category, page, per_page
     */
    public function index(NewsRequest $request): JsonResponse
    {
        return NewsHelper::cachedList(
            $request->categorySlug(),
            $request->page(),
            $request->perPage(),
        );
    }

    /**
     * GET /api/news/{id}
     *
     * Returns one article with full content.
     */
    public function show(NewsRequest $request): JsonResponse
    {
        return NewsHelper::cachedShow($request->newsId());
    }
}
