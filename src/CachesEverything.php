<?php

namespace FilamentCache;

use Illuminate\Support\Facades\Cache;

trait CachesEverything
{
    protected function cacheQuery($query, int $ttl = 300)
    {
        return $query->autoCache($ttl);
    }

    protected function cacheOptions(string $key, callable $callback, int $ttl = 3600)
    {
        return Cache::remember($key, $ttl, $callback);
    }

    protected function cacheWidgetData(array $data, int $ttl = 300): array
    {
        $key = static::class . '_' . auth()->id();
        return Cache::remember($key, $ttl, fn() => $data);
    }
}