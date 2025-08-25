<?php

namespace FilamentCache;

use Illuminate\Support\ServiceProvider;

class FilamentCacheServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/filament-cache.php',
            'filament-cache'
        );
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/filament-cache.php' => config_path('filament-cache.php'),
            ], 'filament-cache-config');
        }
    }
}