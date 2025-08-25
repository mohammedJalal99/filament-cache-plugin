<?php

namespace FilamentCache;

use Filament\Contracts\Plugin;
use Filament\Panel;

class FilamentCachePlugin implements Plugin
{
    protected bool $cachePages = true;
    protected bool $cacheNavigation = true;
    protected bool $cacheQueries = true;
    protected int $ttl = 300;

    public static function make(): static
    {
        return app(static::class);
    }

    public function getId(): string
    {
        return 'filament-cache';
    }

    public function ttl(int $seconds): static
    {
        $this->ttl = $seconds;
        return $this;
    }

    public function disablePageCache(): static
    {
        $this->cachePages = false;
        return $this;
    }

    public function disableNavigationCache(): static
    {
        $this->cacheNavigation = false;
        return $this;
    }

    public function disableQueryCache(): static
    {
        $this->cacheQueries = false;
        return $this;
    }

    public function register(Panel $panel): void
    {
        if ($this->cachePages && config('filament-cache.cache_pages')) {
            $panel->middleware([CacheMiddleware::class]);
        }
    }

    public function boot(Panel $panel): void
    {
        if ($this->cacheQueries && config('filament-cache.cache_queries')) {
            $this->bootQueryCaching();
        }

        if ($this->cacheNavigation && config('filament-cache.cache_navigation')) {
            $this->bootNavigationCaching($panel);
        }
    }

    private function bootQueryCaching(): void
    {
        \Illuminate\Database\Eloquent\Builder::macro('cached', function (int $ttl = null) {
            $ttl = $ttl ?? config('filament-cache.default_ttl');
            $key = 'query_' . md5($this->toSql() . serialize($this->getBindings()));

            try {
                $storeConfig = config('filament-cache.cache_store');
                $store = $storeConfig ? cache()->store($storeConfig) : cache();

                return $store->remember($key, $ttl, fn() => $this->get());
            } catch (\Exception $e) {
                // If caching fails, just return the query result without caching
                \Log::warning('Query cache error: ' . $e->getMessage());
                return $this->get();
            }
        });
    }

    private function bootNavigationCaching(Panel $panel): void
    {
        // Navigation caching logic here
    }
}