<?php

namespace App\Modules\Category\Cache;

use App\Modules\Category\Http\Resources\CategoryResource;
use App\Modules\Category\Models\Category;
use App\Modules\Category\Repositories\CategoryRepository;
use App\Support\Cache\CacheKey;
use App\Support\Cache\CacheService;
use Illuminate\Database\Eloquent\Builder;

/**
 * Category cache layer.
 *
 * Owns category-specific cache rules:
 *   - which key to use
 *   - TTL
 *   - repository fetch + Resource mapping on cache miss
 */
class CategoryCache
{
    public function __construct(
        private readonly CacheService $cache,
        private readonly CategoryRepository $repository,
    ) {}

    /**
     * Return a cached category list.
     *
     * @param  string  $key  Cache key (from CacheKey)
     * @param  (callable(Builder<Category>): Builder<Category>)|null  $scope  Optional query filter
     * @return array<string, mixed>
     */
    public function rememberList(string $key, ?callable $scope = null): array
    {
        return $this->cache->getOrStore(
            $key,
            CacheKey::CATEGORY_TTL,
            fn () => CategoryResource::collection($this->repository->getOrdered($scope))
                ->response()
                ->getData(true),
        );
    }
}
