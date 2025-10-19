<?php

namespace App\Helpers\Cache;

use Illuminate\Support\Facades\Cache as FacadeCache;

class Cache
{
    /**
     * Универсальный remember с опциональными тегами.
     */
    public static function remember(string $key, int $ttl, callable $callback, ?string $tag = null)
    {
        $driver = config('cache.default');

        if ($tag && in_array($driver, ['redis', 'memcached'])) {
            return FacadeCache::tags($tag)->remember($key, $ttl, $callback);
        }

        return FacadeCache::remember($key, $ttl, $callback);
    }

    /**
     * Универсальный forget с опциональными тегами.
     */
    public static function forget(string $key, ?string $tag = null)
    {
        $driver = config('cache.default');

        if ($tag && in_array($driver, ['redis', 'memcached'])) {
            return FacadeCache::tags($tag)->forget($key);
        }

        return FacadeCache::forget($key);
    }
}
