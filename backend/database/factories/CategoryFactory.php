<?php

namespace Database\Factories;

use App\Modules\Category\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(rand(1, 2), true);

        return [
            'name' => ucfirst($name),
            'slug' => Str::slug($name),
            'sort_order' => fake()->numberBetween(1, 100),
            'show_in_menu' => true,
        ];
    }
}
