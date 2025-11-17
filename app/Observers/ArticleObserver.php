<?php

namespace App\Observers;

use App\Contracts\CacheInvalidatorInterface;
use App\Models\Article;

class ArticleObserver
{
    public function __construct(
        private readonly CacheInvalidatorInterface $cacheInvalidator
    ) {}

    /**
     * Handle the Article "created" event.
     */
    public function created(Article $article): void
    {
        $this->invalidateCache($article);
    }

    /**
     * Handle the Article "updated" event.
     */
    public function updated(Article $article): void
    {
        $this->invalidateCache($article);
    }

    /**
     * Handle the Article "deleted" event.
     */
    public function deleted(Article $article): void
    {
        $this->invalidateCache($article);
    }

    /**
     * Handle the Article "restored" event.
     */
    public function restored(Article $article): void
    {
        $this->invalidateCache($article);
    }

    /**
     * Invalidate article cache.
     */
    private function invalidateCache(Article $article): void
    {
        $articleId = is_string($article->id) ? $article->id : null;
        $this->cacheInvalidator->invalidateArticleCache($articleId);
    }
}
