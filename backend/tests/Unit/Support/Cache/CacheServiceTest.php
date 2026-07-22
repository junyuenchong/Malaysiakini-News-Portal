<?php

namespace Tests\Unit\Support\Cache;

use App\Support\Cache\CacheService;
use Illuminate\Support\Facades\Cache;
use RuntimeException;
use Tests\TestCase;

/**
 * Unit tests for App\Support\Cache\CacheService.
 */
class CacheServiceTest extends TestCase
{
    /**
     * getOrStore should return cached data on a normal cache hit/miss.
     */
    public function test_get_or_store_returns_value_from_cache(): void
    {
        Cache::shouldReceive('remember')
            ->once()
            ->with('test-key', 60, \Mockery::type('callable'))
            ->andReturn(['data' => 'cached']);

        $service = new CacheService;

        $result = $service->getOrStore('test-key', 60, fn () => ['data' => 'from-db']);

        $this->assertSame(['data' => 'cached'], $result);
    }

    /**
     * getOrStore should fall back to the callback when the cache store fails.
     */
    public function test_get_or_store_falls_back_to_callback_when_cache_fails(): void
    {
        Cache::shouldReceive('remember')
            ->once()
            ->andThrow(new RuntimeException('Cache unavailable'));

        $service = new CacheService;

        $result = $service->getOrStore('test-key', 60, fn () => ['data' => 'from-db']);

        $this->assertSame(['data' => 'from-db'], $result);
    }

    /**
     * jsonWithCacheHeader should return JSON with a public Cache-Control header.
     */
    public function test_json_with_cache_header_sets_cache_control(): void
    {
        $service = new CacheService;

        $response = $service->jsonWithCacheHeader(['data' => []], 300);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringContainsString('public', $response->headers->get('Cache-Control'));
        $this->assertStringContainsString('max-age=300', $response->headers->get('Cache-Control'));
    }
}
