<?php

namespace App\Cache;

use Illuminate\Support\Facades\Cache;

class RedisCacheKeyGenerator
{
    private const string KEY_REGISTRY = 'cache_key_registry';

    /**
     * Generate a structured cache key.
     *
     * Creates a cache key from prefix and parameters, sorted for consistency.
     * Registers the key for later invalidation by prefix.
     *
     * @param  string  $prefix  The cache key prefix
     * @param  array<string, mixed>  $params  Optional parameters to include in the key
     * @return string The generated cache key
     */
    public function generate(string $prefix, array $params = []): string
    {
        if ($params === []) {
            return $prefix;
        }

        ksort($params);

        $parts = [$prefix];
        foreach ($params as $key => $value) {
            $stringValue = is_scalar($value) ? (string) $value : '';
            $parts[] = "{$key}:{$stringValue}";
        }

        $cacheKey = implode(':', $parts);

        $this->registerKey($prefix, $cacheKey);

        return $cacheKey;
    }

    /**
     * Invalidate all cache keys matching a prefix.
     *
     * Removes all cache entries that were registered under the specified prefix.
     * Falls back to registry-based invalidation for non-Redis stores.
     *
     * @param  string  $prefix  The cache key prefix to invalidate
     */
    public function invalidateByPrefix(string $prefix): void
    {
        $this->fallbackInvalidation($prefix);

        Cache::forget($prefix);
    }

    /**
     * Register a cache key for a prefix.
     *
     * Stores the cache key in a registry for later bulk invalidation.
     * Only used for non-Redis cache stores.
     *
     * @param  string  $prefix  The cache key prefix
     * @param  string  $key  The full cache key to register
     */
    private function registerKey(string $prefix, string $key): void
    {
        $store = Cache::getStore();

        if (! method_exists($store, 'getRedis')) {
            /** @var array<string, array<int, string>> $registry */
            $registry = Cache::get(self::KEY_REGISTRY, []);

            if (! isset($registry[$prefix])) {
                $registry[$prefix] = [];
            }

            $registry[$prefix][] = $key;
            $registry[$prefix] = array_unique($registry[$prefix]);

            Cache::put(self::KEY_REGISTRY, $registry, now()->addHours(24));
        }
    }

    /**
     * Fallback invalidation for non-Redis stores.
     *
     * Uses the key registry to invalidate all keys under a prefix.
     * Required for cache stores that don't support pattern-based deletion.
     *
     * @param  string  $prefix  The cache key prefix to invalidate
     */
    private function fallbackInvalidation(string $prefix): void
    {
        /** @var array<string, array<int, string>> $registry */
        $registry = Cache::get(self::KEY_REGISTRY, []);

        if (! isset($registry[$prefix])) {
            return;
        }

        foreach ($registry[$prefix] as $key) {
            Cache::forget($key);
        }

        unset($registry[$prefix]);
        Cache::put(self::KEY_REGISTRY, $registry, now()->addHours(24));
    }
}
