<?php

namespace App\Repositories;

use App\Contracts\FollowerRepositoryInterface;
use App\Models\Follower;
use Illuminate\Database\Eloquent\Collection;

/**
 * Follower Repository.
 *
 * Handles data access operations for followers.
 */
final readonly class FollowerRepository implements FollowerRepositoryInterface
{
    /**
     * Toggle a follow relationship.
     *
     * @param  string  $followerId  The ID of the user who is following
     * @param  string  $followingId  The ID of the user being followed
     * @return array{following: bool, follower: Follower|null} The follow status and follower model
     */
    public function toggle(string $followerId, string $followingId): array
    {
        $existing = Follower::where('follower_id', $followerId)
            ->where('following_id', $followingId)
            ->first();

        if ($existing !== null) {
            $existing->delete();

            return ['following' => false, 'follower' => null];
        }

        $follower = Follower::create([
            'follower_id' => $followerId,
            'following_id' => $followingId,
        ]);

        return ['following' => true, 'follower' => $follower];
    }

    /**
     * Check if a user is following another user.
     *
     * @param  string  $followerId  The ID of the user who is following
     * @param  string  $followingId  The ID of the user being followed
     * @return bool True if following, false otherwise
     */
    public function isFollowing(string $followerId, string $followingId): bool
    {
        return Follower::where('follower_id', $followerId)
            ->where('following_id', $followingId)
            ->exists();
    }

    /**
     * Get all followers for a user.
     *
     * @param  string  $userId  The user ID to get followers for
     * @return Collection<int, Follower> The collection of followers
     */
    public function getFollowers(string $userId): Collection
    {
        return Follower::where('following_id', $userId)
            ->with('follower:_id,name,username,avatar_url,bio')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get all users a user is following.
     *
     * @param  string  $userId  The user ID to get following list for
     * @return Collection<int, Follower> The collection of users being followed
     */
    public function getFollowing(string $userId): Collection
    {
        return Follower::where('follower_id', $userId)
            ->with('following:_id,name,username,avatar_url,bio')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get follower count for a user.
     *
     * @param  string  $userId  The user ID to get follower count for
     * @return int The number of followers
     */
    public function getFollowerCount(string $userId): int
    {
        return Follower::where('following_id', $userId)->count();
    }

    /**
     * Get following count for a user.
     *
     * @param  string  $userId  The user ID to get following count for
     * @return int The number of users being followed
     */
    public function getFollowingCount(string $userId): int
    {
        return Follower::where('follower_id', $userId)->count();
    }
}
