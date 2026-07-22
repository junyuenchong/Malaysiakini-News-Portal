<?php

namespace App\Support\Cache;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

/**
 * App-cache abstraction layer.
 *
 * Only handles cache operations — keys and TTL live in CacheKey.
 *
 * *Cache classes call getOrStore() — controllers call jsonWithCacheHeader().
 */
class CacheService
{
    /**
     * Return cached value, or build and store it on cache miss.
     *
     * If the cache store is unavailable, runs $callback directly so the
     * request still succeeds via the database.
     *
     * @param  string  $key  Unique cache name (from CacheKey)
     * @param  int  $ttl  Lifetime in seconds (from CacheKey::*_TTL)
     * @param  callable  $callback  Builds the value when cache is empty
     */
    public function getOrStore(string $key, int $ttl, callable $callback): mixed
    {
        try {
            return Cache::remember($key, $ttl, $callback);
        } catch (\Throwable) {
            // Cache down — skip cache and load fresh data from the database
            return $callback();
        }
    }

    /**
     * Return a JSON response with a public Cache-Control header.
     *
     * Controllers use this — services stay free of HTTP concerns.
     *
     * @param  array<string, mixed>  $payload  Response body
     * @param  int  $maxAge  Browser cache lifetime in seconds
     */
    public function jsonWithCacheHeader(array $payload, int $maxAge): JsonResponse
    {
        return response()
            ->json($payload)
            ->header('Cache-Control', 'public, max-age='.$maxAge);
    }
}
