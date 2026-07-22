<?php

namespace App\Support\Http\Concerns;

use Illuminate\Http\JsonResponse;

/**
 * Shared JSON response helper for API controllers.
 *
 * Requires a $cache property (CacheService) on the using class.
 */
trait RespondsWithCachedJson
{
    /**
     * @param  array<string, mixed>  $payload
     */
    protected function cachedResponse(array $payload, int $maxAge): JsonResponse
    {
        return $this->cache->jsonWithCacheHeader($payload, $maxAge);
    }
}
