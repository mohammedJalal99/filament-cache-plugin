<?php

namespace FilamentCache;

use FilamentCache\Commands\ClearCacheCommand;
use Illuminate\Support\ServiceProvider;

class FilamentCacheServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/filament-cache.php',
            'filament-cache'
        );

        // Register the cache clearing command
        if ($this->app->runningInConsole()) {
            $this->commands([
                ClearCacheCommand::class,
            ]);
        }
    }

    public function boot(): void
    {
        // Load helper functions
        require_once __DIR__ . '/helpers.php';

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/filament-cache.php' => config_path('filament-cache.php'),
            ], 'filament-cache-config');
        }

        // Auto-cache Eloquent queries if enabled
        if (config('filament-cache.cache_queries', true)) {
            $this->bootGlobalQueryCaching();
        }
    }

    private function bootGlobalQueryCaching(): void
    {
        // Add cached() macro to all Eloquent builders globally
        \Illuminate\Database\Eloquent\Builder::macro('cached', function (int $ttl = null) {
            return \FilamentCache\AutoCache::cacheQuery($this, $ttl ?? config('filament-cache.ttl_queries', 60));
        });

        // Add cachedCount() macro
        \Illuminate\Database\Eloquent\Builder::macro('cachedCount', function (int $ttl = null) {
            return \FilamentCache\AutoCache::cacheCount($this, $ttl ?? 300);
        });
    }
}