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
FILAMENT_CACHE_STORE=
FILAMENT_CACHE_PAGES=true
FILAMENT_CACHE_NAVIGATION=true  
FILAMENT_CACHE_QUERIES=true

# Advanced settings
FILAMENT_CACHE_FORMS=true
FILAMENT_CACHE_TABLES=true
FILAMENT_CACHE_WIDGETS=true
FILAMENT_CACHE_PERMISSIONS=true
FILAMENT_CACHE_AGGRESSIVE=false

# Specific TTL values for different components
FILAMENT_CACHE_TTL_FORMS=1800        # 30 minutes - forms don't change often
FILAMENT_CACHE_TTL_TABLES=1800       # 30 minutes - table structure
FILAMENT_CACHE_TTL_WIDGETS=300       # 5 minutes - dynamic data
FILAMENT_CACHE_TTL_NAVIGATION=3600   # 1 hour - rarely changes
FILAMENT_CACHE_TTL_PERMISSIONS=3600  # 1 hour - user permissions
FILAMENT_CACHE_TTL_QUERIES=60        # 1 minute - data queries
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

## Usage - No Traits Required!

This plugin now works **without requiring traits**. You can use simple helper functions anywhere in your Filament resources.

### 1. Cache Database Queries

#### Automatic Query Caching
```php
// In your resources - use the cached() macro
class UserResource extends Resource
{
    public static function table(Table $table): Table
    {
        return $table
            ->query(
                User::where('active', true)->cached(120) // Cache for 2 minutes
            )
            ->columns([...]);
    }
}

// Count queries
$totalUsers = User::query()->cachedCount(300); // Cache count for 5 minutes
```

#### Using Helper Functions
```php
// Cache any query result
$activeUsers = filament_cache_query(
    User::where('active', true), 
    60 // TTL in seconds
);

// Cache count queries
$userCount = filament_cache_count(
    User::where('status', 'active'), 
    300
);
```

### 2. Cache Form Options

```php
Forms\Components\Select::make('status')
    ->options(
        filament_cache_options('user_statuses', [
            'active' => 'Active',
            'pending' => 'Pending', 
            'inactive' => 'Inactive'
        ], 1800) // Cache for 30 minutes
    )

Forms\Components\Select::make('department')
    ->options(fn() => 
        filament_cache('department_options', 
            fn() => Department::pluck('name', 'id')->toArray(),
            3600 // Cache for 1 hour
        )
    )
```

### 3. Cache Expensive Calculations

```php
// In table columns
TextColumn::make('total_orders')
    ->getStateUsing(fn($record) => 
        filament_cache("user_orders_{$record->id}", 
            fn() => $record->orders()->count(),
            300 // Cache for 5 minutes
        )
    )

// In widgets
class StatsOverview extends BaseWidget
{
    protected function getCards(): array
    {
        return [
            Stat::make('Total Users', 
                filament_cache('total_users_stat', 
                    fn() => User::count(),
                    300
                )
            ),
            Stat::make('Active Users',
                filament_cache('active_users_stat',
                    fn() => User::where('active', true)->count(),
                    300
                )
            ),
        ];
    }
}
```

### 4. Cache Resource Data

```php
class UserResource extends Resource
{
    // Cache table query
    public static function getEloquentQuery(): Builder
    {
        return filament_cache_query(
            parent::getEloquentQuery()->with('roles', 'department'),
            120 // Cache for 2 minutes
        );
    }

    // Cache relationship options
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('role_id')
                    ->options(
                        filament_cache('role_options',
                            fn() => Role::pluck('name', 'id'),
                            1800 // Cache for 30 minutes
                        )
                    ),
            ]);
    }
}
```

### 5. Available Helper Functions

```php
// Cache any value with automatic key generation
filament_cache(string $key, $callback, int $ttl = null)

// Cache database query results
filament_cache_query($query, int $ttl = 60)

// Cache count queries
filament_cache_count($query, int $ttl = 300)

// Cache select options and arrays
filament_cache_options(string $key, array $options, int $ttl = 1800)

// Forget cached value
filament_cache_forget(string $key)
```

## Cache Management

### Clear Cache Commands

```bash
# Clear all cache
php artisan filament-cache:clear

# Clear specific cache types
php artisan filament-cache:clear --type=pages
php artisan filament-cache:clear --type=queries
php artisan filament-cache:clear --type=navigation
php artisan filament-cache:clear --type=forms
php artisan filament-cache:clear --type=tables
php artisan filament-cache:clear --type=widgets
php artisan filament-cache:clear --type=permissions
```

### Programmatic Cache Management

```php
use FilamentCache\CacheHelper;

// Clear all Filament cache
CacheHelper::clearFilamentCache();

// Clear specific cache types
CacheHelper::clearFormCache();
CacheHelper::clearTableCache();
CacheHelper::clearWidgetCache();
CacheHelper::clearPageCache();
CacheHelper::clearNavigationCache();

// Clear cache for specific user
CacheHelper::clearUserCache($userId);

// Get cache statistics
$stats = CacheHelper::getCacheStats();
```

## Performance Tips

### 1. Choose Appropriate TTL Values
```php
// Static data - longer cache
filament_cache_options('countries', $countries, 3600); // 1 hour

// Dynamic data - shorter cache  
filament_cache_query(Order::recent(), 60); // 1 minute

// User-specific data - medium cache
filament_cache("user_stats_{$userId}", $callback, 300); // 5 minutes
```

### 2. Cache Invalidation
```php
// In your models, clear cache when data changes
class User extends Model
{
    protected static function booted()
    {
        static::saved(function () {
            filament_cache_forget('total_users_stat');
            filament_cache_forget('active_users_stat');
            CacheHelper::clearTableCache();
        });
    }
}
```

### 3. Aggressive Caching Mode
Set `FILAMENT_CACHE_AGGRESSIVE=true` in your .env file to automatically triple all cache times for maximum performance.

## Troubleshooting

### Cache Not Working?
1. Check if caching is enabled: `config('filament-cache.enabled')`
2. Verify cache driver: `php artisan cache:table` (for database driver)
3. Clear all cache: `php artisan cache:clear && php artisan filament-cache:clear`

### Stale Data Issues?
1. Reduce TTL values for frequently changing data
2. Implement cache invalidation in your models
3. Use shorter cache times during development

### Performance Not Improved?
1. Enable query caching: `FILAMENT_CACHE_QUERIES=true`
2. Use Redis cache store: `FILAMENT_CACHE_STORE=redis`
3. Enable aggressive caching: `FILAMENT_CACHE_AGGRESSIVE=true`
4. Monitor cache hit rates with `CacheHelper::getCacheStats()`

## Common Patterns

### Caching Expensive Form Options
```php
Select::make('category_id')
    ->options(fn() => 
        filament_cache('category_tree',
            fn() => Category::with('children')->get()->pluck('full_name', 'id'),
            1800
        )
    )
```

### Caching Dashboard Statistics
```php
protected function getStats(): array
{
    return [
        'total' => filament_cache('dashboard_total', fn() => Model::count(), 300),
        'today' => filament_cache('dashboard_today', fn() => Model::whereDate('created_at', today())->count(), 300),
        'growth' => filament_cache('dashboard_growth', fn() => $this->calculateGrowth(), 600),
    ];
}
```

### Caching Complex Filters
```php
public function getTableFilters(): array
{
    return [
        SelectFilter::make('category')
            ->options(
                filament_cache('filter_categories',
                    fn() => Category::pluck('name', 'id')->toArray(),
                    3600
                )
            ),
    ];
}
```

This approach gives you maximum performance without requiring any traits - just use the helper functions where you need caching!
