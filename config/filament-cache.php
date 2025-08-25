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
    ],
];