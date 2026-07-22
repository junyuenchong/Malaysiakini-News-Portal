<?php

namespace App\Support\Cache\Concerns;

use App\Support\Http\ResourcePayload;

/**
 * Shared cache helper for *Cache classes.
 *
 * Requires a $cache property (CacheService) on the using class.
 */
trait RemembersResourcePayload
{
    /**
     * Cache a Resource/ResourceCollection converted to an array.
     *
     * @param  callable(): mixed  $buildResource
     * @return array<string, mixed>
     */
    protected function rememberResource(string $key, int $ttl, callable $buildResource): array
    {
        return $this->cache->getOrStore(
            $key,
            $ttl,
            fn () => ResourcePayload::toArray($buildResource()),
        );
    }
}
