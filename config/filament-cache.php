<?php

return [
    'enabled' => env('FILAMENT_CACHE_ENABLED', true),

    'default_ttl' => env('FILAMENT_CACHE_TTL', 300),

    'cache_store' => env('FILAMENT_CACHE_STORE', 'default'),

    'cache_pages' => env('FILAMENT_CACHE_PAGES', true),

    'cache_navigation' => env('FILAMENT_CACHE_NAVIGATION', true),

    'cache_queries' => env('FILAMENT_CACHE_QUERIES', true),

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
        'profile'
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
        '_token'
    ],

    // Minimum response size to cache (bytes)
    'min_cache_size' => 100,

    // Maximum response size to cache (bytes) - prevents caching huge responses
    'max_cache_size' => 1024 * 1024, // 1MB
];