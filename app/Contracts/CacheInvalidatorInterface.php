<?php

namespace App\Contracts;

interface CacheInvalidatorInterface
{
    /**
     * Invalidate cache by prefix pattern.
     */
    public function invalidateByPrefix(string $prefix): void;

    /**
     * Invalidate article-related cache.
     */
    public function invalidateArticleCache(?string $articleId = null): void;

    /**
     * Invalidate popular articles cache.
     */
    public function invalidatePopularArticlesCache(): void;
}
