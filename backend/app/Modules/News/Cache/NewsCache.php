<?php

namespace App\Modules\News\Cache;

use App\Modules\News\Http\Resources\NewsResource;
use App\Modules\News\Repositories\NewsRepository;
use App\Support\Cache\CacheKey;
use App\Support\Cache\CacheService;
use App\Support\Cache\Concerns\RemembersResourcePayload;

/**
 * News cache layer.
 *
 * Owns news-specific cache rules:
 *   - which key to use
 *   - TTL
 *   - repository fetch + Resource mapping on cache miss
 */
class NewsCache
{
    use RemembersResourcePayload;

    public function __construct(
        private readonly CacheService $cache,
        private readonly NewsRepository $repository,
    ) {}

    /**
     * GET /api/news — cached paginated list (with optional category filter).
     *
     * @return array<string, mixed>
     */
    public function rememberList(?string $categorySlug, ?int $categoryId, int $page, int $perPage): array
    {
        return $this->rememberPaginated(
            CacheKey::newsList($categorySlug, $page, $perPage),
            fn () => $this->repository->paginate($categoryId, $page, $perPage),
        );
    }

    /**
     * GET /api/news?category=unknown — empty page with valid pagination shape.
     *
     * @return array<string, mixed>
     */
    public function rememberEmptyList(?string $categorySlug, int $page, int $perPage): array
    {
        return $this->rememberPaginated(
            CacheKey::newsList($categorySlug, $page, $perPage),
            fn () => $this->repository->emptyPaginate($page, $perPage),
        );
    }

    /**
     * GET /api/categories/{id}/news — cached news for one category.
     *
     * @return array<string, mixed>
     */
    public function rememberByCategory(int $categoryId, int $page, int $perPage): array
    {
        return $this->rememberPaginated(
            CacheKey::newsByCategory($categoryId, $page, $perPage),
            fn () => $this->repository->paginate($categoryId, $page, $perPage),
        );
    }

    /**
     * GET /api/news/{id} — cached single article with full content.
     *
     * @return array<string, mixed>
     */
    public function rememberDetail(int $id): array
    {
        return $this->rememberResource(
            CacheKey::newsShow($id),
            CacheKey::NEWS_LIST_TTL,
            fn () => new NewsResource($this->repository->findOrFailWithCategory($id)),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function rememberPaginated(string $key, callable $callback): array
    {
        return $this->rememberResource(
            $key,
            CacheKey::NEWS_LIST_TTL,
            fn () => NewsResource::collection($callback()),
        );
    }
}
