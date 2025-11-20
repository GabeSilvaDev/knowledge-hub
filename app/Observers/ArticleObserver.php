<?php

namespace App\Observers;

use App\Contracts\CacheInvalidatorInterface;
use App\Models\Article;

/**
 * Observer for Article model events.
 *
 * Automatically invalidates cache when articles are created, updated, deleted, or restored.
 */
class ArticleObserver
{
    /**
     * Initialize the observer.
     *
     * Constructs the observer with injected cache invalidator dependency.
     *
     * @param  CacheInvalidatorInterface  $cacheInvalidator  Service for invalidating cache entries
     */
    public function __construct(
        private readonly CacheInvalidatorInterface $cacheInvalidator
    ) {}

    /**
     * Handle the Article "created" event.
     *
     * Invalidates article cache when a new article is created.
     *
     * @param  Article  $article  The newly created article
     */
    public function created(Article $article): void
    {
        $this->invalidateCache($article);
    }

    /**
     * Handle the Article "updated" event.
     *
     * Invalidates article cache when an article is updated.
     *
     * @param  Article  $article  The updated article
     */
    public function updated(Article $article): void
    {
        $this->invalidateCache($article);
    }

    /**
     * Handle the Article "deleted" event.
     *
     * Invalidates article cache when an article is deleted (soft delete).
     *
     * @param  Article  $article  The deleted article
     */
    public function deleted(Article $article): void
    {
        $this->invalidateCache($article);
    }

    /**
     * Handle the Article "restored" event.
     *
     * Invalidates article cache when a soft-deleted article is restored.
     *
     * @param  Article  $article  The restored article
     */
    public function restored(Article $article): void
    {
        $this->invalidateCache($article);
    }

    /**
     * Invalidate article cache.
     *
     * Delegates cache invalidation to the cache invalidator service for the specific article.
     *
     * @param  Article  $article  The article to invalidate cache for
     */
    private function invalidateCache(Article $article): void
    {
        $articleId = is_string($article->id) ? $article->id : null;
        $this->cacheInvalidator->invalidateArticleCache($articleId);
    }
}
