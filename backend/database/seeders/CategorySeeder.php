<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Berita Terkini', 'slug' => 'latest', 'sort_order' => 1],
            ['name' => 'Politik', 'slug' => 'politics', 'sort_order' => 2],
            ['name' => 'Ekonomi', 'slug' => 'economy', 'sort_order' => 3],
            ['name' => 'Sukan', 'slug' => 'sports', 'sort_order' => 4],
            ['name' => 'Opini', 'slug' => 'opinion', 'sort_order' => 5],
            ['name' => 'Video', 'slug' => 'video', 'sort_order' => 6],
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
