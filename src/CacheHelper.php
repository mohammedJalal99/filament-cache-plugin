<?php

namespace FilamentCache;

use Illuminate\Support\Facades\Cache;

class CacheHelper
{
    public static function clearFilamentCache(): void
    {
        $store = self::getCacheStore();

        // Clear all cache types
        self::clearByPattern($store, 'filament_page_*');
        self::clearByPattern($store, 'query_*');
        self::clearByPattern($store, 'navigation_*');
        self::clearByPattern($store, 'form_schema_*');
        self::clearByPattern($store, 'table_*');
        self::clearByPattern($store, 'widget_*');
        self::clearByPattern($store, 'options_*');
        self::clearByPattern($store, 'user_permissions_*');
        self::clearByPattern($store, 'component_state_*');
        self::clearByPattern($store, 'model_stats_*');
    }

    public static function clearPageCache(): void
    {
        $store = self::getCacheStore();
        self::clearByPattern($store, 'filament_page_*');
    }

    public static function clearFormCache(): void
    {
        $store = self::getCacheStore();
        self::clearByPattern($store, 'form_schema_*');
    }

    public static function clearTableCache(): void
    {
        $store = self::getCacheStore();
        self::clearByPattern($store, 'table_*');
    }

    public static function clearWidgetCache(): void
    {
        $store = self::getCacheStore();
        self::clearByPattern($store, 'widget_*');
    }

    public static function clearNavigationCache(): void
    {
        $store = self::getCacheStore();
        self::clearByPattern($store, 'navigation_*');
    }

    public static function clearUserCache($userId = null): void
    {
        $userId = $userId ?? auth()->id() ?? 'guest';
        $store = self::getCacheStore();

        // Clear all caches for specific user
        self::clearByPattern($store, "*_{$userId}*");
        self::clearByPattern($store, "*_{$userId}");
    }

    public static function warmCache(): void
    {
        if (!config('filament-cache.warm_cache_on_boot', false)) {
            return;
        }

        $resourcesToWarm = config('filament-cache.resources_to_warm', []);

        foreach ($resourcesToWarm as $resource) {
            try {
                // Pre-warm commonly accessed methods
                if (method_exists($resource, 'getFormSchema')) {
                    $resource::getFormSchema();
                }
                if (method_exists($resource, 'getTableColumns')) {
                    $resource::getTableColumns();
                }
            } catch (\Exception $e) {
                \Log::warning("Failed to warm cache for {$resource}: " . $e->getMessage());
            }
        }
    }

    public static function getCacheStats(): array
    {
        $store = self::getCacheStore();

        // This is a simplified version - in production you might want more detailed stats
        return [
            'cache_store' => config('filament-cache.cache_store', 'default'),
            'enabled' => config('filament-cache.enabled', true),
            'aggressive_caching' => config('filament-cache.aggressive_caching', false),
            'estimated_keys' => self::estimateCacheKeys(),
        ];
    }

    private static function estimateCacheKeys(): int
    {
        try {
            $store = self::getCacheStore();

            if (method_exists($store->getStore(), 'connection')) {
                $redis = $store->getStore()->connection();
                return count($redis->keys('filament_*')) +
                       count($redis->keys('query_*')) +
                       count($redis->keys('navigation_*')) +
                       count($redis->keys('form_*')) +
                       count($redis->keys('table_*')) +
                       count($redis->keys('widget_*'));
            }
        } catch (\Exception $e) {
            // Ignore errors
        }

        return 0;
    }

    public static function invalidateCacheForUser($userId = null): void
    {
        $userId = $userId ?? auth()->id() ?? 'guest';
        $store = self::getCacheStore();

        self::clearByPattern($store, "filament_page_*{$userId}*");
        self::clearByPattern($store, "user_permissions_{$userId}");
        self::clearByPattern($store, "widget_*_{$userId}");
        self::clearByPattern($store, "form_schema_*_{$userId}");
        self::clearByPattern($store, "table_*_{$userId}");
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
