<?php

namespace Tests\Unit\Projections;

use App\Http\Projections\CategoryProjection;
use App\Models\Category;
use Illuminate\Http\Request;
use Tests\TestCase;

/**
 * Unit tests for CategoryProjection.
 */
class CategoryProjectionTest extends TestCase
{
    /** CategoryProjection should expose only the fields needed by the UI. */
    public function test_transforms_category_to_expected_json_shape(): void
    {
        $category = new Category;
        $category->forceFill([
            'id' => 5,
            'name' => 'Economy',
            'slug' => 'economy',
            'sort_order' => 2,
            'show_in_menu' => true,
        ]);

        $payload = (new CategoryProjection($category))->resolve(Request::create('/'));

        $this->assertSame([
            'id' => 5,
            'name' => 'Economy',
            'slug' => 'economy',
            'sort_order' => 2,
        ], $payload);

        $this->assertArrayNotHasKey('show_in_menu', $payload);
    }
}
