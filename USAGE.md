# Filament Cache Plugin - Usage Guide

## Installation & Setup

1. **Publish the configuration file:**
```bash
php artisan vendor:publish --tag=filament-cache-config
```

2. **Configure your environment (.env):**
```env
FILAMENT_CACHE_ENABLED=true
FILAMENT_CACHE_TTL=300
FILAMENT_CACHE_STORE=default
FILAMENT_CACHE_PAGES=true
FILAMENT_CACHE_NAVIGATION=true  
FILAMENT_CACHE_QUERIES=true
```

3. **Register the plugin in your Filament Panel Provider:**
```php
use FilamentCache\FilamentCachePlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugins([
            FilamentCachePlugin::make()
                ->ttl(600) // Optional: override default TTL
        ]);
}
```

## Common Issues & Solutions

### Blank Page Problem (FIXED)
The main issue was that the middleware was incorrectly caching HTTP Response objects. The fix includes:
- Proper response content extraction
- Response validation before caching
- Better cache key generation
- Route exclusion logic

### Clear Cache Commands

1. **Clear all cache:**
```bash
php artisan filament-cache:clear
```

2. **Clear specific cache types:**
```bash
php artisan filament-cache:clear --type=pages
php artisan filament-cache:clear --type=queries
php artisan filament-cache:clear --type=navigation
```

### Programmatic Cache Management

```php
use FilamentCache\CacheHelper;

// Clear all Filament cache
CacheHelper::clearFilamentCache();

// Clear only page cache
CacheHelper::clearPageCache();

// Clear cache for specific user
CacheHelper::invalidateCacheForUser(auth()->id());
```

## Configuration Options

The plugin now includes comprehensive configuration in `config/filament-cache.php`:

- `enabled`: Enable/disable caching globally
- `default_ttl`: Default cache time-to-live in seconds
- `cache_store`: Laravel cache store to use
- `excluded_routes`: Routes to never cache
- `excluded_route_patterns`: Route patterns to exclude
- `skip_cache_params`: Query parameters that prevent caching
- `min_cache_size`: Minimum response size to cache
- `max_cache_size`: Maximum response size to cache

## Troubleshooting

1. **Still getting blank pages?**
   - Clear your cache: `php artisan cache:clear`
   - Disable caching temporarily: Set `FILAMENT_CACHE_ENABLED=false`
   - Check Laravel logs for errors

2. **Cache not working?**
   - Verify your cache driver is properly configured
   - Check if routes are being excluded
   - Ensure the middleware is registered

3. **Performance issues?**
   - Reduce TTL for frequently changing content
   - Exclude dynamic routes from caching
   - Monitor cache hit rates
