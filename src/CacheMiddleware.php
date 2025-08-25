<?php

namespace FilamentCache;

use Closure;
use Illuminate\Support\Facades\Cache;

class CacheMiddleware
{
    public function handle($request, Closure $next)
    {
        if (!$request->isMethod('GET') || $request->user()?->cannot('cache')) {
            return $next($request);
        }

        $key = 'page_' . md5($request->fullUrl() . auth()->id());

        return Cache::remember($key, 60, fn() => $next($request));
    }
}