<?php

namespace App\Observers;

use App\Contracts\Neo4jRepositoryInterface;
use App\Models\User;

/**
 * Observer for User model events for Neo4j synchronization.
 *
 * Automatically syncs user data to Neo4j when users are created, updated, or deleted.
 */
class UserNeo4jObserver
{
    public function __construct(
        private readonly Neo4jRepositoryInterface $neo4jRepository,
    ) {}

    /**
     * Handle the User "created" event.
     *
     * @param  User  $user  The created user
     */
    public function created(User $user): void
    {
        $this->syncToNeo4j($user);
    }

    /**
     * Handle the User "updated" event.
     *
     * @param  User  $user  The updated user
     */
    public function updated(User $user): void
    {
        $this->syncToNeo4j($user);
    }

    /**
     * Handle the User "deleted" event.
     *
     * @param  User  $user  The deleted user
     */
    public function deleted(User $user): void
    {
        $userId = is_string($user->id) ? $user->id : null;
        if ($userId !== null) {
            $this->neo4jRepository->deleteUser($userId);
        }
    }

    /**
     * Sync user data to Neo4j.
     *
     * @param  User  $user  The user to sync
     */
    private function syncToNeo4j(User $user): void
    {
        $this->neo4jRepository->syncUser([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'username' => $user->username,
        ]);
    }
}
