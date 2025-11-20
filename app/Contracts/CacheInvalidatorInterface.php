<?php

namespace App\Contracts;

/**
 * Cache invalidator contract.
 *
 * Defines the interface for cache invalidation operations.
 */
interface CacheInvalidatorInterface
{
    /**
     * Invalidate cache by prefix pattern.
     *
     * Removes all cache entries matching the specified prefix.
     *
     * @param  string  $prefix  The cache key prefix to invalidate
     */
    public function invalidateByPrefix(string $prefix): void;

    /**
     * Invalidate article-related cache.
     *
     * Removes cache entries for a specific article and popular articles.
     *
     * @param  string|null  $articleId  Optional article ID for specific invalidation
     */
    public function invalidateArticleCache(?string $articleId = null): void;

    /**
     * Invalidate popular articles cache.
     *
     * Removes all cached popular articles listings.
     */
    public function invalidatePopularArticlesCache(): void;
}
