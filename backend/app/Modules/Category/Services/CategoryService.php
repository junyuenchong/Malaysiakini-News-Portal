<?php

namespace App\Modules\Category\Services;

use App\Modules\Category\Cache\CategoryCache;
use App\Support\Cache\CacheKey;
use Illuminate\Database\Eloquent\Builder;

/**
 * Category business-logic layer.
 *
 * Thin orchestrator — delegates caching to CategoryCache.
 * Returns plain arrays; controllers handle HTTP response.
 *
 * Routes:
 *   GET /api/categories  → getAll()
 *   GET /api/menu        → getMenu()
 */
class CategoryService
{
    public function __construct(
        private readonly CategoryCache $categoryCache,
    ) {}

    /**
     * GET /api/categories — all categories, cached.
     *
     * @return array<string, mixed>
     */
    public function getAll(): array
    {
        return $this->categoryCache->rememberList(CacheKey::categoriesAll());
    }

    /**
     * GET /api/menu — menu-visible categories only, cached.
     *
     * @return array<string, mixed>
     */
    public function getMenu(): array
    {
        return $this->categoryCache->rememberList(
            CacheKey::categoriesMenu(),
            fn (Builder $query) => $query->where('show_in_menu', true),
        );
    }
}
