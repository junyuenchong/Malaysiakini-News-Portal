<?php

namespace Tests\Unit\Modules\Category\Resources;

use App\Modules\Category\Http\Resources\CategoryResource;
use App\Modules\Category\Models\Category;
use Illuminate\Http\Request;
use Tests\TestCase;

/**
 * Unit tests for App\Modules\Category\Http\Resources\CategoryResource.
 *
 * Verifies the public JSON shape without hitting the database or HTTP stack.
 */
class CategoryResourceTest extends TestCase
{
    /**
     * CategoryResource should expose only API fields.
     */
    public function test_transforms_category_to_expected_json_shape(): void
    {
        // Build an in-memory model — no DB needed
        $category = new Category;
        $category->forceFill([
            'id' => 5,
            'name' => 'Business',
            'slug' => 'business',
            'sort_order' => 2,
            'show_in_menu' => true, // internal field — must not leak to API
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
