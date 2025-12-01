<?php

namespace App\Observers;

use App\Contracts\Neo4jRepositoryInterface;
use App\Models\Like;

/**
 * Observer for Like model events for Neo4j synchronization.
 *
 * Automatically syncs like relationships to Neo4j when likes are created or deleted.
 */
class LikeNeo4jObserver
{
    public function __construct(
        private readonly Neo4jRepositoryInterface $neo4jRepository,
    ) {}

    /**
     * Handle the Like "created" event.
     */
    public function created(Like $like): void
    {
        $this->neo4jRepository->syncLike(
            (string) $like->user_id,
            (string) $like->article_id
        );
    }

    /**
     * Handle the Like "deleted" event.
     */
    public function deleted(Like $like): void
    {
        $this->neo4jRepository->deleteLike(
            (string) $like->user_id,
            (string) $like->article_id
        );
    }
}
