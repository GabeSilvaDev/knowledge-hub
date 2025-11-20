<?php

namespace App\Services;

use App\Contracts\FollowerRepositoryInterface;
use App\Contracts\FollowerServiceInterface;
use App\Models\Follower;
use Illuminate\Database\Eloquent\Collection;
use InvalidArgumentException;

/**
 * Follower Service.
 *
 * Handles business logic for follower operations.
 */
final readonly class FollowerService implements FollowerServiceInterface
{
    public function __construct(
        private FollowerRepositoryInterface $followerRepository,
    ) {}

    /**
     * Toggle a follow relationship.
     *
     * @return array{following: bool, follower: Follower|null}
     */
    public function toggleFollow(string $followerId, string $followingId): array
    {
        if ($followerId === $followingId) {
            throw new InvalidArgumentException('Usuário não pode seguir a si mesmo.');
        }

        return $this->followerRepository->toggle($followerId, $followingId);
    }

    /**
     * Check if a user is following another user.
     */
    public function isFollowing(string $followerId, string $followingId): bool
    {
        return $this->followerRepository->isFollowing($followerId, $followingId);
    }

    /**
     * Get all followers for a user.
     *
     * @return Collection<int, Follower>
     */
    public function getFollowers(string $userId): Collection
    {
        return $this->followerRepository->getFollowers($userId);
    }

    /**
     * Get all users a user is following.
     *
     * @return Collection<int, Follower>
     */
    public function getFollowing(string $userId): Collection
    {
        return $this->followerRepository->getFollowing($userId);
    }

    /**
     * Get follower and following counts for a user.
     *
     * @return array{followers_count: int, following_count: int}
     */
    public function getCounts(string $userId): array
    {
        return [
            'followers_count' => $this->followerRepository->getFollowerCount($userId),
            'following_count' => $this->followerRepository->getFollowingCount($userId),
        ];
    }
}
