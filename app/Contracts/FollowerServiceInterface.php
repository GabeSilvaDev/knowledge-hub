<?php

namespace App\Contracts;

use App\Models\Follower;
use Illuminate\Database\Eloquent\Collection;

/**
 * Interface for Follower Service.
 *
 * Defines the contract for follower business logic operations.
 */
interface FollowerServiceInterface
{
    /**
     * Toggle a follow relationship.
     *
     * @return array{following: bool, follower: Follower|null}
     */
    public function toggleFollow(string $followerId, string $followingId): array;

    /**
     * Check if a user is following another user.
     */
    public function isFollowing(string $followerId, string $followingId): bool;

    /**
     * Get all followers for a user.
     *
     * @return Collection<int, Follower>
     */
    public function getFollowers(string $userId): Collection;

    /**
     * Get all users a user is following.
     *
     * @return Collection<int, Follower>
     */
    public function getFollowing(string $userId): Collection;

    /**
     * Get follower and following counts for a user.
     *
     * @return array{followers_count: int, following_count: int}
     */
    public function getCounts(string $userId): array;
}
