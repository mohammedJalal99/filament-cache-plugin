<?php

namespace FilamentCache;

use Illuminate\Support\Facades\Cache;

trait CachesEverything
{
    protected function cacheQuery($query, int $ttl = 300)
    {
        return $query->cached($ttl);
    }

    protected function cacheOptions(string $key, callable $callback, int $ttl = 3600)
    {
        $cacheKey = 'options_' . $key . '_' . auth()->id() . '_' . app()->getLocale();
        return $this->getCacheStore()->remember($cacheKey, $ttl, $callback);
    }

    protected function cacheWidgetData(array $data, int $ttl = 300): array
    {
        $cacheKey = 'widget_' . static::class . '_' . auth()->id();
        return $this->getCacheStore()->remember($cacheKey, $ttl, fn() => $data);
    }

    // Cache form schema
    protected function cacheFormSchema(array $schema, int $ttl = 1800): array
    {
        $cacheKey = 'form_schema_' . static::class . '_' . auth()->id();
        return $this->getCacheStore()->remember($cacheKey, $ttl, fn() => $schema);
    }

    // Cache table columns
    protected function cacheTableColumns(array $columns, int $ttl = 1800): array
    {
        $cacheKey = 'table_columns_' . static::class . '_' . auth()->id();
        return $this->getCacheStore()->remember($cacheKey, $ttl, fn() => $columns);
    }

    // Cache table filters
    protected function cacheTableFilters(array $filters, int $ttl = 1800): array
    {
        $cacheKey = 'table_filters_' . static::class . '_' . auth()->id();
        return $this->getCacheStore()->remember($cacheKey, $ttl, fn() => $filters);
    }

    // Cache table actions
    protected function cacheTableActions(array $actions, int $ttl = 1800): array
    {
        $cacheKey = 'table_actions_' . static::class . '_' . auth()->id();
        return $this->getCacheStore()->remember($cacheKey, $ttl, fn() => $actions);
    }

    // Cache resource pages
    protected function cacheResourcePages(array $pages, int $ttl = 3600): array
    {
        $cacheKey = 'resource_pages_' . static::class . '_' . auth()->id();
        return $this->getCacheStore()->remember($cacheKey, $ttl, fn() => $pages);
    }

    // Cache navigation items
    protected function cacheNavigationItems(array $items, int $ttl = 3600): array
    {
        $cacheKey = 'navigation_items_' . static::class . '_' . auth()->id();
        return $this->getCacheStore()->remember($cacheKey, $ttl, fn() => $items);
    }

    // Cache dashboard widgets
    protected function cacheDashboardWidgets(array $widgets, int $ttl = 300): array
    {
        $cacheKey = 'dashboard_widgets_' . static::class . '_' . auth()->id();
        return $this->getCacheStore()->remember($cacheKey, $ttl, fn() => $widgets);
    }

    // Cache relation managers
    protected function cacheRelationManagers(array $managers, int $ttl = 1800): array
    {
        $cacheKey = 'relation_managers_' . static::class . '_' . auth()->id();
        return $this->getCacheStore()->remember($cacheKey, $ttl, fn() => $managers);
    }

    // Cache page actions
    protected function cachePageActions(array $actions, int $ttl = 1800): array
    {
        $cacheKey = 'page_actions_' . static::class . '_' . auth()->id();
        return $this->getCacheStore()->remember($cacheKey, $ttl, fn() => $actions);
    }

    // Cache info lists
    protected function cacheInfoLists(array $lists, int $ttl = 1800): array
    {
        $cacheKey = 'info_lists_' . static::class . '_' . auth()->id();
        return $this->getCacheStore()->remember($cacheKey, $ttl, fn() => $lists);
    }

    // Cache bulk actions
    protected function cacheBulkActions(array $actions, int $ttl = 1800): array
    {
        $cacheKey = 'bulk_actions_' . static::class . '_' . auth()->id();
        return $this->getCacheStore()->remember($cacheKey, $ttl, fn() => $actions);
    }

    // Cache table query with all modifiers
    protected function cacheTableQuery($query, array $filters = [], string $search = '', int $ttl = 60)
    {
        $cacheKey = 'table_query_' . static::class . '_' . md5(serialize([
            'filters' => $filters,
            'search' => $search,
            'user_id' => auth()->id(),
            'sql' => $query->toSql(),
            'bindings' => $query->getBindings()
        ]));

        return $this->getCacheStore()->remember($cacheKey, $ttl, fn() => $query->get());
    }

    // Cache component states
    protected function cacheComponentState(string $component, $state, int $ttl = 300)
    {
        $cacheKey = 'component_state_' . $component . '_' . auth()->id();
        return $this->getCacheStore()->remember($cacheKey, $ttl, fn() => $state);
    }

    // Cache user permissions
    protected function cacheUserPermissions(int $ttl = 3600): array
    {
        $cacheKey = 'user_permissions_' . auth()->id();
        return $this->getCacheStore()->remember($cacheKey, $ttl, function () {
            $user = auth()->user();
            return [
                'can_view' => method_exists($user, 'can') ? $user->can('view', static::class) : true,
                'can_create' => method_exists($user, 'can') ? $user->can('create', static::class) : true,
                'can_update' => method_exists($user, 'can') ? $user->can('update', static::class) : true,
                'can_delete' => method_exists($user, 'can') ? $user->can('delete', static::class) : true,
            ];
        });
    }

    // Cache model counts for stats
    protected function cacheModelStats(string $model, int $ttl = 300): array
    {
        $cacheKey = 'model_stats_' . str_replace('\\', '_', $model) . '_' . auth()->id();
        return $this->getCacheStore()->remember($cacheKey, $ttl, function () use ($model) {
            return [
                'total' => $model::count(),
                'active' => $model::where('status', 'active')->count() ?? 0,
                'recent' => $model::where('created_at', '>=', now()->subDays(7))->count(),
            ];
        });
    }

    // Clear all caches for this resource
    protected function clearResourceCache(): void
    {
        $patterns = [
            'form_schema_' . static::class . '_' . auth()->id(),
            'table_columns_' . static::class . '_' . auth()->id(),
            'table_filters_' . static::class . '_' . auth()->id(),
            'table_actions_' . static::class . '_' . auth()->id(),
            'resource_pages_' . static::class . '_' . auth()->id(),
            'relation_managers_' . static::class . '_' . auth()->id(),
            'page_actions_' . static::class . '_' . auth()->id(),
            'user_permissions_' . auth()->id(),
        ];

        foreach ($patterns as $pattern) {
            $this->getCacheStore()->forget($pattern);
        }
    }

    private function getCacheStore()
    {
        try {
            $storeConfig = config('filament-cache.cache_store');
            return $storeConfig ? Cache::store($storeConfig) : Cache::store();
        } catch (\Exception $e) {
            return Cache::store('array');
        }
    }
}