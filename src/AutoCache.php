<?php

namespace FilamentCache;

use Illuminate\Support\Facades\Cache;

class AutoCache
{
    protected static function getCacheStore()
    {
        try {
            $storeConfig = config('filament-cache.cache_store');
            return $storeConfig ? Cache::store($storeConfig) : Cache::store();
        } catch (\Exception $e) {
            return Cache::store('array');
        }
    }

    public static function remember(string $key, $callback, int $ttl = null)
    {
        if (!config('filament-cache.enabled', true)) {
            return is_callable($callback) ? $callback() : $callback;
        }

        $ttl = $ttl ?? config('filament-cache.default_ttl', 300);
        $fullKey = 'filament_auto_' . $key . '_' . (auth()->id() ?? 'guest');

        return self::getCacheStore()->remember($fullKey, $ttl, function() use ($callback) {
            return is_callable($callback) ? $callback() : $callback;
        });
    }

    public static function rememberForever(string $key, $callback)
    {
        if (!config('filament-cache.enabled', true)) {
            return is_callable($callback) ? $callback() : $callback;
        }

        $fullKey = 'filament_forever_' . $key . '_' . (auth()->id() ?? 'guest');

        return self::getCacheStore()->rememberForever($fullKey, function() use ($callback) {
            return is_callable($callback) ? $callback() : $callback;
        });
    }

    public static function cacheQuery($query, int $ttl = 60)
    {
        if (!config('filament-cache.cache_queries', true)) {
            return $query->get();
        }

        $key = 'query_' . md5($query->toSql() . serialize($query->getBindings()) . (auth()->id() ?? 'guest'));

        return self::getCacheStore()->remember($key, $ttl, function() use ($query) {
            return $query->get();
        });
    }

    public static function cacheCount($query, int $ttl = 300)
    {
        if (!config('filament-cache.cache_queries', true)) {
            return $query->count();
        }

        $key = 'count_' . md5($query->toSql() . serialize($query->getBindings()) . (auth()->id() ?? 'guest'));

        return self::getCacheStore()->remember($key, $ttl, function() use ($query) {
            return $query->count();
        });
    }

    public static function forget(string $key)
    {
        $fullKey = 'filament_auto_' . $key . '_' . (auth()->id() ?? 'guest');
        return self::getCacheStore()->forget($fullKey);
    }

    public static function flush()
    {
        return self::getCacheStore()->flush();
    }
}
