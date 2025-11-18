<?php

namespace App\Cache;

use App\Contracts\CacheInvalidatorInterface;
use App\Exceptions\CacheInvalidationException;
use Exception;
use Illuminate\Support\Facades\Log;

class RedisCacheInvalidator implements CacheInvalidatorInterface
{
    public function __construct(
        private readonly RedisCacheKeyGenerator $keyGenerator
    ) {}

    /**
     * Invalidate cache by prefix pattern.
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
     */
    public function invalidatePopularArticlesCache(): void
    {
        $this->invalidateByPrefix('popular_articles');
    }
}
