<?php

namespace Database\Seeders;

use App\Modules\Category\Models\Category;
use App\Modules\News\Models\News;
use Illuminate\Database\Seeder;

class NewsSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::query()->get();
        $total = 100;
        $base = intdiv($total, $categories->count());
        $extra = $total % $categories->count();

        // Spread 100 articles evenly (e.g. 6 categories → 17+17+17+17+16+16)
        foreach ($categories as $index => $category) {
            $count = $base + ($index < $extra ? 1 : 0);

            News::factory()
                ->count($count)
                ->create(['category_id' => $category->id]);
        }
    }
}
