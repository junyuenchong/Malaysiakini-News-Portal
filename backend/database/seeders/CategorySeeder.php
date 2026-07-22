<?php

namespace Database\Seeders;

use App\Modules\Category\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Malaysia', 'slug' => 'malaysia', 'sort_order' => 1],
            ['name' => 'World', 'slug' => 'world', 'sort_order' => 2],
            ['name' => 'Business', 'slug' => 'business', 'sort_order' => 3],
            ['name' => 'Sports', 'slug' => 'sports', 'sort_order' => 4],
            ['name' => 'Opinion', 'slug' => 'opinion', 'sort_order' => 5],
            ['name' => 'Life', 'slug' => 'life', 'sort_order' => 6],
        ];

        foreach ($categories as $category) {
            Category::query()->updateOrCreate(
                ['slug' => $category['slug']],
                [
                    'name' => $category['name'],
                    'sort_order' => $category['sort_order'],
                    'show_in_menu' => true,
                ],
            );
        }
    }
}
