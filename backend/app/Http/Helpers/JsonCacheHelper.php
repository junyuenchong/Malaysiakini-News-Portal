<?php

namespace App\Http\Helpers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

/**
 * Shared cache + JSON response helper.
 *
 * Used by CategoryHelper and NewsHelper so both follow
 * the same cache and response pattern:
 *   1. Read from app cache (or build on miss)
 *   2. Return JSON with a browser Cache-Control header
 */
class JsonCacheHelper
{
    /**
     * Load data from cache, or run $resolver on cache miss.
     *
     * Returns JSON with a Cache-Control header so browsers
     * can cache the response too.
     *
     * @param  string  $cacheKey  Unique cache name for this response
     * @param  int  $ttl  App cache lifetime in seconds
     * @param  callable  $resolver  Builds the payload on cache miss
     * @param  int|null  $maxAge  Browser cache header (defaults to $ttl)
     */
    public static function respond(string $cacheKey, int $ttl, callable $resolver, ?int $maxAge = null): JsonResponse
    {
        // Read from cache, or build payload with $resolver
        $payload = Cache::remember($cacheKey, $ttl, $resolver);

        // Tell browsers how long they may reuse this response
        return response()
            ->json($payload)
            ->header('Cache-Control', 'public, max-age='.($maxAge ?? $ttl));
    }
}
