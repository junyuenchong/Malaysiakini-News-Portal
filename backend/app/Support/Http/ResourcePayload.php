<?php

namespace App\Support\Http;

/**
 * Converts API Resources into plain arrays for caching.
 */
class ResourcePayload
{
    /**
     * @param  mixed  $resource  JsonResource or ResourceCollection
     * @return array<string, mixed>
     */
    public static function toArray(mixed $resource): array
    {
        return $resource->response()->getData(true);
    }
}
