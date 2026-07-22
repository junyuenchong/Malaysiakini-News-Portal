<?php

namespace App\Support\Cache;

use App\Modules\Category\Services\CategoryService;
use App\Modules\News\Services\NewsService;

/**
 * Pre-loads common API cache keys so the first browser request is fast.
 */
class ApiCacheWarmer
{
    private const DEFAULT_PAGE = 1;

    private const DEFAULT_PER_PAGE = 12;

    public function __construct(
        private readonly CategoryService $categoryService,
        private readonly NewsService $newsService,
    ) {}

    /**
     * Warm the most common read endpoints used by the Angular app.
     */
    public function warm(): void
    {
        $this->categoryService->getAll();
        $this->categoryService->getMenu();

        $homeNews = $this->newsService->getList(null, self::DEFAULT_PAGE, self::DEFAULT_PER_PAGE);
        $this->warmCategoryLists();
        $this->warmFirstArticleDetail($homeNews);
    }

    /**
     * @param  array<string, mixed>  $homeNews
     */
    private function warmFirstArticleDetail(array $homeNews): void
    {
        $firstId = $homeNews['data'][0]['id'] ?? null;

        if (! is_int($firstId)) {
            return;
        }

        $this->newsService->getById($firstId);
    }

    private function warmCategoryLists(): void
    {
        $categories = $this->categoryService->getAll();

        foreach ($categories['data'] ?? [] as $category) {
            if (! is_array($category) || ! isset($category['slug'])) {
                continue;
            }

            $this->newsService->getList(
                (string) $category['slug'],
                self::DEFAULT_PAGE,
                self::DEFAULT_PER_PAGE,
            );
        }
    }
}
