<?php

namespace App\Contracts;

use App\Models\Follower;
use Illuminate\Database\Eloquent\Collection;

/**
 * Interface for Follower Repository.
 *
 * Defines the contract for follower data access operations.
 */
interface FollowerRepositoryInterface
{
    /**
     * Toggle a follow relationship.
     * If following, unfollow. If not following, follow.
     *
     * @return array{following: bool, follower: Follower|null}
     */
    public function toggle(string $followerId, string $followingId): array;

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
     * Get follower count for a user.
     */
    public function getFollowerCount(string $userId): int;

    /**
     * Get following count for a user.
     */
    public function getFollowingCount(string $userId): int;
}
