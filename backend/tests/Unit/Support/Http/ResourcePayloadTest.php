<?php

namespace Tests\Unit\Support\Http;

use App\Modules\Category\Http\Resources\CategoryResource;
use App\Modules\Category\Models\Category;
use App\Support\Http\ResourcePayload;
use Tests\TestCase;

/**
 * Unit tests for App\Support\Http\ResourcePayload.
 */
class ResourcePayloadTest extends TestCase
{
    /**
     * ResourcePayload should convert a Resource into a plain array.
     */
    public function test_to_array_converts_resource_to_plain_array(): void
    {
        $category = new Category;
        $category->forceFill([
            'id' => 1,
            'name' => 'World',
            'slug' => 'world',
            'sort_order' => 1,
        ]);

        $payload = ResourcePayload::toArray(new CategoryResource($category));

        $this->assertSame([
            'data' => [
                'id' => 1,
                'name' => 'World',
                'slug' => 'world',
                'sort_order' => 1,
            ],
        ], $payload);
    }
}
