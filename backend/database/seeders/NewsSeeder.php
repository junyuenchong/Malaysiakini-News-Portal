<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\News;
use Illuminate\Database\Seeder;

class NewsSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::query()->get();

        foreach ($categories as $category) {
            News::factory()
                ->count(8)
                ->create(['category_id' => $category->id]);
        }

        News::factory()
            ->count(3)
            ->featured()
            ->create([
                'category_id' => $categories->first()->id,
            ]);
    }
}
