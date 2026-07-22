<?php

namespace App\Modules\News\Services;

use App\Modules\Category\Repositories\CategoryRepository;
use App\Modules\News\Cache\NewsCache;

/**
 * News business-logic layer.
 *
 * Thin orchestrator — handles business rules (slug resolve, 404),
 * delegates caching to NewsCache.
 * Returns plain arrays; controllers handle HTTP response.
 *
 * Routes:
 *   GET /api/news                  → getList()
 *   GET /api/news/{id}             → getById()
 *   GET /api/categories/{id}/news  → getByCategoryId()
 */
class NewsService
{
    public function __construct(
        private readonly NewsCache $newsCache,
        private readonly CategoryRepository $categoryRepository,
    ) {}

    /**
     * GET /api/news — cached paginated news list.
     *
     * @return array<string, mixed>
     */
    public function getList(?string $categorySlug, int $page, int $perPage): array
    {
        if ($categorySlug === null) {
            return $this->newsCache->rememberList(null, null, $page, $perPage);
        }

        // Resolve slug to id before querying news (avoids join)
        $categoryId = $this->categoryRepository->findIdBySlug($categorySlug);

        // Unknown slug — return empty pagination, not 404
        if ($categoryId === null) {
            return $this->newsCache->rememberEmptyList($categorySlug, $page, $perPage);
        }

        return $this->newsCache->rememberList($categorySlug, $categoryId, $page, $perPage);
    }

    /**
     * GET /api/categories/{id}/news — cached news for one category.
     *
     * @return array<string, mixed>
     */
    public function getByCategoryId(int $categoryId, int $page, int $perPage): array
    {
        // Fail fast with 404 when category id is invalid
        $this->categoryRepository->findOrFail($categoryId);

        return $this->newsCache->rememberByCategory($categoryId, $page, $perPage);
    }

    /**
     * GET /api/news/{id} — cached single article with full content.
     *
     * @return array<string, mixed>
     */
    public function getById(int $id): array
    {
        return $this->newsCache->rememberDetail($id);
    }
}
