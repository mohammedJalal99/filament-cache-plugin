<?php

namespace FilamentCache;

use Illuminate\Support\Facades\Cache;

class CacheHelper
{
    public static function clearFilamentCache(): void
    {
        $store = Cache::store(config('filament-cache.cache_store', 'default'));

        // Clear page cache
        self::clearByPattern($store, 'filament_page_*');

        // Clear query cache
        self::clearByPattern($store, 'query_*');

        // Clear navigation cache
        self::clearByPattern($store, 'navigation_*');
    }

    public static function clearPageCache(): void
    {
        $store = Cache::store(config('filament-cache.cache_store', 'default'));
        self::clearByPattern($store, 'filament_page_*');
    }

    public static function invalidateCacheForUser($userId = null): void
    {
        $userId = $userId ?? auth()->id() ?? 'guest';
        $store = Cache::store(config('filament-cache.cache_store', 'default'));

        // This is a simplified approach - in production you might want more sophisticated cache tagging
        self::clearByPattern($store, "filament_page_*{$userId}*");
    }

    private static function clearByPattern($store, string $pattern): void
    {
        try {
            // For Redis stores
            if (method_exists($store->getStore(), 'connection')) {
                $redis = $store->getStore()->connection();
                $keys = $redis->keys($pattern);
                if (!empty($keys)) {
                    $redis->del($keys);
                }
            } else {
                // For other stores, flush all cache (not ideal but safe)
                $store->flush();
            }
        } catch (\Exception $e) {
            // Fallback: clear all cache
            $store->flush();
        }
    }
}
