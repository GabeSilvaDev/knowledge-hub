<?php

namespace App\Cache;

use Illuminate\Support\Facades\Cache;

class RedisCacheKeyGenerator
{
    private const string KEY_REGISTRY = 'cache_key_registry';

    /**
     * Generate a structured cache key.
     *
     * @param  array<string, mixed>  $params
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
     */
    public function invalidateByPrefix(string $prefix): void
    {
        $this->fallbackInvalidation($prefix);

        Cache::forget($prefix);
    }

    /**
     * Register a cache key for a prefix.
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
     */
    private function fallbackInvalidation(string $prefix): void
    {
        /** @var array<string, array<int, string>> $registry */
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
