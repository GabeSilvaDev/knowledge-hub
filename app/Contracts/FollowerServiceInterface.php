<?php

namespace App\Contracts;

use App\Exceptions\SelfFollowException;
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
     * @param  string  $followerId  The ID of the user who is following
     * @param  string  $followingId  The ID of the user being followed
     * @return array{following: bool, follower: Follower|null} The follow status and follower model
     *
     * @throws SelfFollowException When user tries to follow themselves
     */
    public function toggleFollow(string $followerId, string $followingId): array;

    /**
     * Check if a user is following another user.
     *
     * @param  string  $followerId  The ID of the user who is following
     * @param  string  $followingId  The ID of the user being followed
     * @return bool True if following, false otherwise
     */
    public function isFollowing(string $followerId, string $followingId): bool;

    /**
     * Get all followers for a user.
     *
     * @param  string  $userId  The user ID to get followers for
     * @return Collection<int, Follower> The collection of followers
     */
    public function getFollowers(string $userId): Collection;

    /**
     * Get all users a user is following.
     *
     * @param  string  $userId  The user ID to get following list for
     * @return Collection<int, Follower> The collection of users being followed
     */
    public function getFollowing(string $userId): Collection;

    /**
     * Get follower and following counts for a user.
     *
     * @param  string  $userId  The user ID to get counts for
     * @return array{followers_count: int, following_count: int} The follower and following counts
     */
    public function getCounts(string $userId): array;
}
