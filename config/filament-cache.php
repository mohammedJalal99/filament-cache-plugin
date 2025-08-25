<?php

return [
    'enabled' => env('FILAMENT_CACHE_ENABLED', true),

    'default_ttl' => env('FILAMENT_CACHE_TTL', 300),

    // Use 'file' as default instead of 'default' - more reliable
    'cache_store' => env('FILAMENT_CACHE_STORE', null),

    'cache_pages' => env('FILAMENT_CACHE_PAGES', true),

    'cache_navigation' => env('FILAMENT_CACHE_NAVIGATION', true),

    'cache_queries' => env('FILAMENT_CACHE_QUERIES', true),

    // New performance optimization settings
    'cache_forms' => env('FILAMENT_CACHE_FORMS', true),

    'cache_tables' => env('FILAMENT_CACHE_TABLES', true),

    'cache_widgets' => env('FILAMENT_CACHE_WIDGETS', true),

    'cache_permissions' => env('FILAMENT_CACHE_PERMISSIONS', true),

    'cache_resources' => env('FILAMENT_CACHE_RESOURCES', true),

    // Different TTL values for different types of content
    'ttl_forms' => env('FILAMENT_CACHE_TTL_FORMS', 1800),        // 30 minutes - forms don't change often
    'ttl_tables' => env('FILAMENT_CACHE_TTL_TABLES', 1800),      // 30 minutes - table structure
    'ttl_widgets' => env('FILAMENT_CACHE_TTL_WIDGETS', 300),     // 5 minutes - dynamic data
    'ttl_navigation' => env('FILAMENT_CACHE_TTL_NAVIGATION', 3600), // 1 hour - rarely changes
    'ttl_permissions' => env('FILAMENT_CACHE_TTL_PERMISSIONS', 3600), // 1 hour - user permissions
    'ttl_queries' => env('FILAMENT_CACHE_TTL_QUERIES', 60),      // 1 minute - data queries

    // Aggressive caching options for maximum performance
    'aggressive_caching' => env('FILAMENT_CACHE_AGGRESSIVE', false),

    // When aggressive caching is enabled, use longer TTLs
    'aggressive_ttl_multiplier' => 3,

    'excluded_routes' => [
        'filament.admin.auth.login',
        'filament.admin.auth.logout',
        'filament.admin.auth.register',
        'filament.admin.auth.password.request',
        'filament.admin.auth.password.reset',
        'filament.admin.auth.email-verification.prompt',
        'filament.admin.auth.email-verification.verify',
    ],

    // Skip caching for routes containing these patterns
    'excluded_route_patterns' => [
        'livewire',
        'login',
        'logout',
        'password',
        'register',
        'verify',
        'two-factor',
        'profile',
        'notifications',
        'global-search',
    ],

    // Skip caching pages with these query parameters
    'skip_cache_params' => [
        'search',
        'filter',
        'sort',
        'page',
        'action',
        'component',
        'livewire',
        '_token',
        'tableSearch',
        'tableFilters',
        'tableSortColumn',
        'tableSortDirection',
    ],

    // Minimum response size to cache (bytes)
    'min_cache_size' => 100,

    // Maximum response size to cache (bytes) - prevents caching huge responses
    'max_cache_size' => 1024 * 1024, // 1MB

    // Cache warming settings
    'warm_cache_on_boot' => env('FILAMENT_CACHE_WARM_ON_BOOT', false),

    // Pre-cache these resources on application boot
    'resources_to_warm' => [
        // Add your resource classes here, e.g.:
        // App\Filament\Resources\UserResource::class,
    ],
];