<?php

namespace App\Observers;

use App\Contracts\Neo4jRepositoryInterface;
use App\Models\Follower;

/**
 * Observer for Follower model events for Neo4j synchronization.
 *
 * Automatically syncs follow relationships to Neo4j when followers are created or deleted.
 */
class FollowerNeo4jObserver
{
    public function __construct(
        private readonly Neo4jRepositoryInterface $neo4jRepository,
    ) {}

    /**
     * Handle the Follower "created" event.
     *
     * @param  Follower  $follower  The created follower
     */
    public function created(Follower $follower): void
    {
        $this->neo4jRepository->syncFollow(
            (string) $follower->follower_id,
            (string) $follower->following_id
        );
    }

    /**
     * Handle the Follower "deleted" event.
     *
     * @param  Follower  $follower  The deleted follower
     */
    public function deleted(Follower $follower): void
    {
        $this->neo4jRepository->deleteFollow(
            (string) $follower->follower_id,
            (string) $follower->following_id
        );
    }
}
