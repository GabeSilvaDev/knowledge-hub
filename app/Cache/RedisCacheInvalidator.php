<?php

namespace App\Cache;

use App\Contracts\CacheInvalidatorInterface;
use App\Exceptions\CacheInvalidationException;
use Exception;
use Illuminate\Support\Facades\Log;

class RedisCacheInvalidator implements CacheInvalidatorInterface
{
    /**
     * Initialize the cache invalidator.
     *
     * Constructs the invalidator with injected key generator dependency.
     *
     * @param  RedisCacheKeyGenerator  $keyGenerator  Generator for cache key management
     */
    public function __construct(
        private readonly RedisCacheKeyGenerator $keyGenerator
    ) {}

    /**
     * Invalidate cache by prefix pattern.
     *
     * Delegates to the key generator to remove all cache entries matching the prefix.
     * Logs warnings on failure and throws exception.
     *
     * @param  string  $prefix  The cache key prefix to invalidate
     *
     * @throws CacheInvalidationException If invalidation fails
     */
    public function invalidateByPrefix(string $prefix): void
    {
        try {
            $this->keyGenerator->invalidateByPrefix($prefix);
        } catch (Exception $e) {
            Log::warning('Falha ao invalidar cache por prefixo', [
                'prefix' => $prefix,
                'exception' => $e::class,
                'message' => $e->getMessage(),
            ]);

            throw CacheInvalidationException::deletionFailed($prefix);
        }
    }

    /**
     * Invalidate article-related cache.
     *
     * Removes cache entries for a specific article and refreshes popular articles cache.
     *
     * @param  string|null  $articleId  Optional article ID to invalidate specific article cache
     */
    public function invalidateArticleCache(?string $articleId = null): void
    {
        if ($articleId !== null) {
            $this->invalidateByPrefix("article:{$articleId}");
        }

        $this->invalidatePopularArticlesCache();
    }

    /**
     * Invalidate popular articles cache.
     *
     * Removes all cached popular articles listings.
     */
    public function invalidatePopularArticlesCache(): void
    {
        $this->invalidateByPrefix('popular_articles');
    }
}
