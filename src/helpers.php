<?php

if (!function_exists('filament_cache')) {
    /**
     * Cache any value with automatic key generation
     */
    function filament_cache(string $key, $callback, int $ttl = null)
    {
        return \FilamentCache\AutoCache::remember($key, $callback, $ttl);
    }
}

if (!function_exists('filament_cache_query')) {
    /**
     * Cache database query results
     */
    function filament_cache_query($query, int $ttl = 60)
    {
        return \FilamentCache\AutoCache::cacheQuery($query, $ttl);
    }
}

if (!function_exists('filament_cache_count')) {
    /**
     * Cache count queries
     */
    function filament_cache_count($query, int $ttl = 300)
    {
        return \FilamentCache\AutoCache::cacheCount($query, $ttl);
    }
}

if (!function_exists('filament_cache_forget')) {
    /**
     * Forget cached value
     */
    function filament_cache_forget(string $key)
    {
        return \FilamentCache\AutoCache::forget($key);
    }
}

if (!function_exists('filament_cache_options')) {
    /**
     * Cache select options and similar arrays
     */
    function filament_cache_options(string $key, array $options, int $ttl = 1800)
    {
        return filament_cache("options_{$key}", $options, $ttl);
    }
}
