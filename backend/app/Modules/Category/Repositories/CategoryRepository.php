<?php

namespace App\Modules\Category\Repositories;

use App\Modules\Category\Models\Category;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * Category data-access layer.
 *
 * Owns all Eloquent queries for categories.
 * Services call this class — controllers never touch the DB directly.
 */
class CategoryRepository
{
    /**
     * Look up a category id by its URL slug.
     *
     * @param  string  $slug  Category slug from query string
     */
    public function findIdBySlug(string $slug): ?int
    {
        return Category::query()
            ->where('slug', $slug)
            ->value('id');
    }

    /**
     * Find a category by primary key or throw 404.
     *
     * @param  int  $id  Category primary key
     */
    public function findOrFail(int $id): Category
    {
        return Category::query()->findOrFail($id);
    }

    /**
     * Return categories with an optional query scope applied.
     *
     * @param  (callable(Builder<Category>): Builder<Category>)|null  $scope
     * @return Collection<int, Category>
     */
    public function getOrdered(?callable $scope = null): Collection
    {
        // Select only fields used by CategoryResource
        $query = Category::query()
            ->select(Category::API_COLUMNS)
            ->orderBy('sort_order');

        // Apply optional filter (e.g. show_in_menu = true)
        if ($scope !== null) {
            $query = $scope($query);
        }

        return $query->get();
    }
}
