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
     * @return array{following: bool, follower: Follower|null}
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
     * @return Collection<int, Follower>
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
     * @return Collection<int, Follower>
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
     */
    public function getFollowerCount(string $userId): int
    {
        return Follower::where('following_id', $userId)->count();
    }

    /**
     * Get following count for a user.
     */
    public function getFollowingCount(string $userId): int
    {
        return Follower::where('follower_id', $userId)->count();
    }
}
