<?php

namespace App\Services;

use App\Contracts\FeedRepositoryInterface;
use App\Contracts\FeedServiceInterface;
use App\Contracts\FollowerRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Feed Service.
 *
 * Handles business logic for feed operations.
 */
final readonly class FeedService implements FeedServiceInterface
{
    public function __construct(
        private FeedRepositoryInterface $feedRepository,
        private FollowerRepositoryInterface $followerRepository,
    ) {}

    /**
     * Get public feed (for visitors).
     *
     * Returns most popular articles based on views, likes, and comments.
     *
     * @param  int  $perPage  The number of articles per page
     * @return LengthAwarePaginator<int, \App\Models\Article> The paginated articles
     */
    public function getPublicFeed(int $perPage = 15): LengthAwarePaginator
    {
        return $this->feedRepository->getPopularArticles($perPage);
    }

    /**
     * Get personalized feed for authenticated users.
     *
     * Prioritizes:
     * 1. Articles from followed users
     * 2. Popular articles from tags the user interacts with
     * 3. General popular articles
     *
     * @param  string  $userId  The authenticated user ID
     * @param  int  $perPage  The number of articles per page
     * @return LengthAwarePaginator<int, \App\Models\Article> The paginated articles
     */
    public function getPersonalizedFeed(string $userId, int $perPage = 15): LengthAwarePaginator
    {
        $followingIds = $this->followerRepository->getFollowing($userId)
            ->pluck('following_id')
            ->all();

        /** @var array<int, string> $followingIdsArray */
        $followingIdsArray = array_values($followingIds);

        return $this->feedRepository->getPersonalizedArticles($followingIdsArray, $perPage);
    }
}
