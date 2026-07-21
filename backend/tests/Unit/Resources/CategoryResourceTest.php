<?php

namespace Tests\Unit\Resources;

use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;
use Tests\TestCase;

class CategoryResourceTest extends TestCase
{
    public function test_transforms_category_to_expected_json_shape(): void
    {
        $category = new Category;
        $category->forceFill([
            'id' => 5,
            'name' => 'Business',
            'slug' => 'business',
            'sort_order' => 2,
            'show_in_menu' => true,
        ]);

        $payload = (new CategoryResource($category))->resolve(Request::create('/'));

        $this->assertSame([
            'id' => 5,
            'name' => 'Business',
            'slug' => 'business',
            'sort_order' => 2,
        ], $payload);

        $this->assertArrayNotHasKey('show_in_menu', $payload);
    }
}
