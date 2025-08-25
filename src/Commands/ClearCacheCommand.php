<?php

namespace FilamentCache\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ClearCacheCommand extends Command
{
    protected $signature = 'filament-cache:clear {--type=all : Type of cache to clear (all, pages, queries, navigation)}';

    protected $description = 'Clear Filament cache';

    public function handle()
    {
        $type = $this->option('type');
        $store = Cache::store(config('filament-cache.cache_store', 'default'));

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
            case 'all':
            default:
                $this->clearCacheByPattern($store, 'filament_*');
                $this->clearCacheByPattern($store, 'query_*');
                $this->clearCacheByPattern($store, 'navigation_*');
                $this->info('All Filament cache cleared successfully.');
                break;
        }

        return 0;
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
