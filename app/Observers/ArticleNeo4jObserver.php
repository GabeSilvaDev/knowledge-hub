<?php

namespace App\Observers;

use App\Contracts\Neo4jRepositoryInterface;
use App\Models\Article;

/**
 * Observer for Article model events for Neo4j synchronization.
 *
 * Automatically syncs article data to Neo4j when articles are created, updated, or deleted.
 */
class ArticleNeo4jObserver
{
    public function __construct(
        private readonly Neo4jRepositoryInterface $neo4jRepository,
    ) {}

    /**
     * Handle the Article "created" event.
     */
    public function created(Article $article): void
    {
        $this->syncToNeo4j($article);
    }

    /**
     * Handle the Article "updated" event.
     */
    public function updated(Article $article): void
    {
        $this->syncToNeo4j($article);
    }

    /**
     * Handle the Article "deleted" event.
     */
    public function deleted(Article $article): void
    {
        $articleId = is_string($article->id) ? $article->id : null;
        if ($articleId !== null) {
            $this->neo4jRepository->deleteArticle($articleId);
        }
    }

    /**
     * Handle the Article "restored" event.
     */
    public function restored(Article $article): void
    {
        $this->syncToNeo4j($article);
    }

    /**
     * Sync article data to Neo4j.
     */
    private function syncToNeo4j(Article $article): void
    {
        if ($article->status !== 'published') {
            return;
        }

        $this->neo4jRepository->syncArticle([
            'id' => $article->id,
            'title' => $article->title,
            'slug' => $article->slug,
            'status' => $article->status,
            'author_id' => $article->author_id,
            'view_count' => $article->view_count,
            'like_count' => $article->like_count,
            'tags' => $article->tags ?? [],
            'categories' => $article->categories ?? [],
        ]);
    }
}
