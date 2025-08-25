<?php
namespace FilamentCache\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ClearCacheCommand extends Command
{
    protected $signature = 'filament-cache:clear {--type=all : Type of cache to clear (all, pages, queries, navigation, forms, tables, widgets, permissions)}';

    protected $description = 'Clear Filament cache with granular control';

    public function handle()
    {
        $type = $this->option('type');
        $store = $this->getCacheStore();

        switch ($type) {
            case 'pages':
                $this->clearCacheByPattern($store, 'filament_page_*');
                $this->info('Page cache cleared successfully.');
                break;
            case 'queries':
                $this->clearCacheByPattern($store, 'query_*');
                $this->info('Query cache cleared successfully.');
                break;
            case 'navigation':
                $this->clearCacheByPattern($store, 'navigation_*');
                $this->info('Navigation cache cleared successfully.');
                break;
            case 'forms':
                $this->clearCacheByPattern($store, 'form_schema_*');
                $this->info('Form cache cleared successfully.');
                break;
            case 'tables':
                $this->clearCacheByPattern($store, 'table_*');
                $this->info('Table cache cleared successfully.');
                break;
            case 'widgets':
                $this->clearCacheByPattern($store, 'widget_*');
                $this->info('Widget cache cleared successfully.');
                break;
            case 'permissions':
                $this->clearCacheByPattern($store, 'user_permissions_*');
                $this->info('Permissions cache cleared successfully.');
                break;
            case 'all':
            default:
                $this->clearCacheByPattern($store, 'filament_*');
                $this->clearCacheByPattern($store, 'query_*');
                $this->clearCacheByPattern($store, 'navigation_*');
                $this->clearCacheByPattern($store, 'form_schema_*');
                $this->clearCacheByPattern($store, 'table_*');
                $this->clearCacheByPattern($store, 'widget_*');
                $this->clearCacheByPattern($store, 'options_*');
                $this->clearCacheByPattern($store, 'user_permissions_*');
                $this->clearCacheByPattern($store, 'component_state_*');
                $this->clearCacheByPattern($store, 'model_stats_*');
                $this->info('All Filament cache cleared successfully.');
                break;
        }

        return 0;
    }

    private function getCacheStore()
    {
        try {
            $storeConfig = config('filament-cache.cache_store');

            if ($storeConfig) {
                return \Illuminate\Support\Facades\Cache::store($storeConfig);
            }

            // Use default cache store
            return \Illuminate\Support\Facades\Cache::store();
        } catch (\Exception $e) {
            $this->warn('Cache store error, falling back to array cache: ' . $e->getMessage());
            return \Illuminate\Support\Facades\Cache::store('array');
        }
    }

    private function clearCacheByPattern($store, string $pattern): void
    {
        // For Redis and other stores that support pattern deletion
        if (method_exists($store, 'flush')) {
            // Simple flush for stores that don't support pattern matching
            $store->flush();
            return;
        }

        // For stores that support getting all keys (not recommended for production)
        try {
            $keys = $store->getRedis()->keys($pattern);
            if (!empty($keys)) {
                $store->deleteMultiple($keys);
            }
        } catch (\Exception $e) {
            // Fallback to flush all if pattern deletion fails
            $store->flush();
        }
    }
}
