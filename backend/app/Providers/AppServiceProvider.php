<?php

namespace App\Providers;

use App\Support\Cache\CacheService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

/**
 * App service provider.
 *
 * register() — bind shared services into the container
 * boot()     — app-wide startup config (not domain business rules)
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register shared services.
     *
     * CacheService is a singleton so every controller/cache class
     * reuses one instance instead of creating a new one each time.
     */
    public function register(): void
    {
        $this->app->singleton(CacheService::class);
    }

    /**
     * Bootstrap app-wide behaviour.
     *
     * In local/testing, fail when a relation is lazy-loaded.
     * This catches N+1 queries early during development.
     */
    public function boot(): void
    {
        Model::preventLazyLoading(! $this->app->isProduction());
    }
}
