<?php

namespace Database\Factories;

use App\Modules\Category\Models\Category;
use App\Modules\News\Models\News;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<News>
 */
class NewsFactory extends Factory
{
    protected $model = News::class;

    public function definition(): array
    {
        $title = fake()->sentence(rand(4, 10));

        return [
            'category_id' => Category::factory(),
            'title' => rtrim($title, '.'),
            'slug' => Str::slug($title).'-'.fake()->unique()->numerify('####'),
            'summary' => fake()->paragraph(2),
            'content' => collect(range(1, rand(3, 6)))
                ->map(fn () => '<p>'.fake()->paragraph(rand(3, 6)).'</p>')
                ->implode(''),
            'image_url' => null, // Set to local cached URL after seeding
            'author' => fake()->name(),
            'published_at' => fake()->dateTimeBetween('-30 days', 'now'),
            'is_featured' => fake()->boolean(15),
        ];
    }

    public function featured(): static
    {
        return $this->state(fn () => ['is_featured' => true]);
    }
}
