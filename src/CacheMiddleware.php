<?php

namespace FilamentCache;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class CacheMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Only cache GET requests
        if (!$request->isMethod('GET')) {
            return $next($request);
        }

        // Skip caching if disabled
        if (!config('filament-cache.enabled', true) || !config('filament-cache.cache_pages', true)) {
            return $next($request);
        }

        // Skip excluded routes
        $excludedRoutes = config('filament-cache.excluded_routes', []);
        if (in_array($request->route()?->getName(), $excludedRoutes)) {
            return $next($request);
        }

        // Skip if user has specific permissions or is authenticated in certain contexts
        if ($this->shouldSkipCache($request)) {
            return $next($request);
        }

        // Generate cache key
        $cacheKey = $this->generateCacheKey($request);
        $ttl = config('filament-cache.default_ttl', 300);

        // Try to get cached response
        $cachedResponse = Cache::store(config('filament-cache.cache_store', 'default'))
            ->get($cacheKey);

        if ($cachedResponse !== null) {
            return response($cachedResponse['content'])
                ->withHeaders($cachedResponse['headers'])
                ->setStatusCode($cachedResponse['status']);
        }

        // Get fresh response
        $response = $next($request);

        // Only cache successful responses
        if ($response->getStatusCode() === 200 && $this->shouldCacheResponse($response)) {
            $cacheData = [
                'content' => $response->getContent(),
                'headers' => $response->headers->all(),
                'status' => $response->getStatusCode()
            ];

            Cache::store(config('filament-cache.cache_store', 'default'))
                ->put($cacheKey, $cacheData, $ttl);
        }

        return $response;
    }

    private function shouldSkipCache(Request $request): bool
    {
        // Check route patterns
        $routeName = $request->route()?->getName() ?? '';
        $excludedPatterns = config('filament-cache.excluded_route_patterns', []);
        foreach ($excludedPatterns as $pattern) {
            if (str_contains($routeName, $pattern) || str_contains($request->path(), $pattern)) {
                return true;
            }
        }

        // Skip if there are query parameters that indicate dynamic content
        $skipParams = config('filament-cache.skip_cache_params', []);
        foreach ($skipParams as $param) {
            if ($request->has($param)) {
                return true;
            }
        }

        return false;
    }

    private function shouldCacheResponse($response): bool
    {
        // Don't cache if response contains certain headers
        if ($response->headers->has('Set-Cookie')) {
            return false;
        }

        $contentLength = strlen($response->getContent());
        $minSize = config('filament-cache.min_cache_size', 100);
        $maxSize = config('filament-cache.max_cache_size', 1024 * 1024);

        // Don't cache if content is too small or too large
        if ($contentLength < $minSize || $contentLength > $maxSize) {
            return false;
        }

        // Don't cache responses with error indicators
        $content = $response->getContent();
        if (str_contains($content, 'error') || str_contains($content, 'exception')) {
            return false;
        }

        return true;
    }

    private function generateCacheKey(Request $request): string
    {
        $factors = [
            $request->url(),
            $request->query(),
            auth()->id() ?? 'guest',
            app()->getLocale()
        ];

        return 'filament_page_' . md5(serialize($factors));
    }
}