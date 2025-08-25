<?php

namespace FilamentCache;

use Illuminate\Support\Facades\Cache;

class CacheHelper
{
    public static function clearFilamentCache(): void
    {
        $store = self::getCacheStore();

        // Clear page cache
        self::clearByPattern($store, 'filament_page_*');

        // Clear query cache
        self::clearByPattern($store, 'query_*');

        // Clear navigation cache
        self::clearByPattern($store, 'navigation_*');
    }

    public static function clearPageCache(): void
    {
        $store = self::getCacheStore();
        self::clearByPattern($store, 'filament_page_*');
    }

    public static function invalidateCacheForUser($userId = null): void
    {
        $userId = $userId ?? auth()->id() ?? 'guest';
        $store = self::getCacheStore();

        // This is a simplified approach - in production you might want more sophisticated cache tagging
        self::clearByPattern($store, "filament_page_*{$userId}*");
    }

    private static function getCacheStore()
    {
        try {
            $storeConfig = config('filament-cache.cache_store');

            if ($storeConfig) {
                return Cache::store($storeConfig);
            }

            // Use default cache store
            return Cache::store();
        } catch (\Exception $e) {
            // Fallback to array cache if all else fails
            return Cache::store('array');
        }
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
