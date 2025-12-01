<?php

namespace App\Services;

use App\Contracts\ArticleRepositoryInterface;
use App\Contracts\FollowerRepositoryInterface;
use App\Contracts\UserRankingServiceInterface;
use App\Contracts\UserRepositoryInterface;
use App\DTOs\UserRankingDTO;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Redis;

/**
 * User Ranking Service.
 *
 * Manages user influence ranking using Redis Sorted Sets.
 * Implements RF-051: Influence-Based User Ranking.
 */
class UserRankingService implements UserRankingServiceInterface
{
    private const string RANKING_KEY = 'users:ranking:influence';

    private const int TTL_DAYS = 90;

    /** Influence score weights */
    private const float WEIGHT_FOLLOWERS = 2.0;

    private const float WEIGHT_VIEWS = 0.5;

    private const float WEIGHT_LIKES = 1.0;

    private const float WEIGHT_COMMENTS = 0.8;

    private const float WEIGHT_ARTICLES = 1.5;

    /**
     * Initialize the User Ranking Service.
     *
     * @param  UserRepositoryInterface  $userRepository  Repository for user data access
     * @param  FollowerRepositoryInterface  $followerRepository  Repository for follower data
     * @param  ArticleRepositoryInterface  $articleRepository  Repository for article data
     */
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly FollowerRepositoryInterface $followerRepository,
        private readonly ArticleRepositoryInterface $articleRepository,
    ) {}

    /**
     * Calculate and update influence score for a user.
     *
     * @param  string  $userId  The user ID to calculate score for
     * @return float The calculated influence score
     */
    public function calculateInfluenceScore(string $userId): float
    {
        $user = $this->userRepository->findById($userId);

        if (! $user instanceof User) {
            return 0.0;
        }

        $followersCount = $this->followerRepository->getFollowerCount($userId);
        $stats = $this->getUserArticleStats($user);

        $score = (
            ($followersCount * self::WEIGHT_FOLLOWERS) +
            ($stats['total_views'] * self::WEIGHT_VIEWS) +
            ($stats['total_likes'] * self::WEIGHT_LIKES) +
            ($stats['total_comments'] * self::WEIGHT_COMMENTS) +
            ($stats['articles_count'] * self::WEIGHT_ARTICLES)
        );

        return round($score, 2);
    }

    /**
     * Update influence score for a user in Redis.
     *
     * @param  string  $userId  The user ID
     * @param  float  $score  The influence score
     */
    public function updateScore(string $userId, float $score): void
    {
        Redis::zadd(self::RANKING_KEY, $score, $userId);
        $this->updateExpiration();
    }

    /**
     * Increment user score by a specified amount.
     *
     * @param  string  $userId  The user ID
     * @param  float  $increment  Amount to increment (default: 1.0)
     */
    public function incrementScore(string $userId, float $increment = 1.0): void
    {
        Redis::zincrby(self::RANKING_KEY, $increment, $userId);
        $this->updateExpiration();
    }

    /**
     * Get top ranked users by influence.
     *
     * @param  int  $limit  Maximum number of users to return (default: 10)
     * @return Collection<int, array{user_id: string, score: float}> Top users with scores
     */
    public function getTopUsers(int $limit = 10): Collection
    {
        /** @var array<string, float> $results */
        $results = Redis::zrevrange(self::RANKING_KEY, 0, $limit - 1, ['withscores' => true]);

        /** @var Collection<int, array{user_id: string, score: float}> $users */
        $users = collect();

        foreach ($results as $userId => $score) {
            $users->push([
                'user_id' => $userId,
                'score' => (float) $score,
            ]);
        }

        return $users;
    }

    /**
     * Get user rank position (1-based).
     *
     * @param  string  $userId  The user ID
     * @return int|null The rank (1-based) or null if not ranked
     */
    public function getUserRank(string $userId): ?int
    {
        $rank = Redis::zrevrank(self::RANKING_KEY, $userId);

        if ($rank === false) {
            return null;
        }

        return (int) $rank + 1;
    }

    /**
     * Get user influence score.
     *
     * @param  string  $userId  The user ID
     * @return float The influence score
     */
    public function getUserScore(string $userId): float
    {
        $score = Redis::zscore(self::RANKING_KEY, $userId);

        if ($score === false) {
            return 0.0;
        }

        return (float) $score;
    }

    /**
     * Remove user from ranking.
     *
     * @param  string  $userId  The user ID to remove
     */
    public function removeUser(string $userId): void
    {
        Redis::zrem(self::RANKING_KEY, $userId);
    }

    /**
     * Reset entire ranking (clear all scores).
     */
    public function resetRanking(): void
    {
        Redis::del(self::RANKING_KEY);
    }

    /**
     * Sync MongoDB user data to Redis ranking.
     */
    public function syncFromDatabase(): void
    {
        $this->resetRanking();

        $users = User::all();

        foreach ($users as $user) {
            $userId = $user->id;
            assert(is_string($userId));

            $score = $this->calculateInfluenceScore($userId);
            $this->updateScore($userId, $score);
        }

        $this->updateExpiration();
    }

    /**
     * Get ranking statistics.
     *
     * @return array{total_users: int, total_score: float, top_score: float, average_score: float}
     */
    public function getStatistics(): array
    {
        $totalUsers = (int) Redis::zcard(self::RANKING_KEY);

        /** @var array<string, float> $topUser */
        $topUser = Redis::zrevrange(self::RANKING_KEY, 0, 0, ['withscores' => true]);
        $topScore = empty($topUser) ? 0.0 : array_values($topUser)[0];

        /** @var array<string, float> $allScores */
        $allScores = Redis::zrange(self::RANKING_KEY, 0, -1, ['withscores' => true]);
        $totalScore = 0.0;

        foreach ($allScores as $score) {
            $totalScore += (float) $score;
        }

        $averageScore = $totalUsers > 0 ? round($totalScore / $totalUsers, 2) : 0.0;

        return [
            'total_users' => $totalUsers,
            'total_score' => round($totalScore, 2),
            'top_score' => $topScore,
            'average_score' => $averageScore,
        ];
    }

    /**
     * Get enriched top users with user details.
     *
     * @param  int  $limit  Maximum number of users (default: 10)
     * @return Collection<int, array{rank: int, user_id: string, score: float, user: array<string, mixed>|null}> Enriched ranking
     */
    public function getEnrichedTopUsers(int $limit = 10): Collection
    {
        $ranking = $this->getTopUsers($limit);

        /** @var array<int, string> $userIds */
        $userIds = $ranking->pluck('user_id')->toArray();

        /** @var Collection<string, User> $users */
        $users = User::whereIn('_id', $userIds)->get()->keyBy('id');

        /** @var Collection<int, array{rank: int, user_id: string, score: float, user: array<string, mixed>|null}> $enrichedRanking */
        $enrichedRanking = $ranking->map(function (array $item, int|string $index) use ($users): array {
            /** @var User|null $user */
            $user = $users->get($item['user_id']);

            return [
                'rank' => $index + 1,
                'user_id' => $item['user_id'],
                'score' => $item['score'],
                'user' => $user instanceof User ? [
                    'name' => $user->name,
                    'username' => $user->username,
                    'avatar_url' => $user->avatar_url,
                    'bio' => $user->bio,
                    'created_at' => $user->created_at?->toISOString(),
                ] : null,
            ];
        });

        return $enrichedRanking;
    }

    /**
     * Get enriched user ranking info.
     *
     * @param  string  $userId  The user ID
     * @return UserRankingDTO User ranking data transfer object
     */
    public function getEnrichedUserRanking(string $userId): UserRankingDTO
    {
        $user = $this->userRepository->findById($userId);

        if (! $user instanceof User) {
            return new UserRankingDTO(
                userId: $userId,
                rank: null,
                score: 0.0,
            );
        }

        $rank = $this->getUserRank($userId);
        $score = $this->getUserScore($userId);
        $followersCount = $this->followerRepository->getFollowerCount($userId);
        $stats = $this->getUserArticleStats($user);

        return new UserRankingDTO(
            userId: $userId,
            rank: $rank,
            score: $score,
            followersCount: $followersCount,
            articlesCount: $stats['articles_count'],
            totalViews: $stats['total_views'],
            totalLikes: $stats['total_likes'],
            totalComments: $stats['total_comments'],
            user: [
                'name' => $user->name,
                'username' => $user->username,
                'avatar_url' => $user->avatar_url,
                'bio' => $user->bio,
                'created_at' => $user->created_at?->toISOString(),
            ],
        );
    }

    /**
     * Recalculate and update ranking for a specific user.
     *
     * @param  string  $userId  The user ID to recalculate
     */
    public function recalculateUser(string $userId): void
    {
        $score = $this->calculateInfluenceScore($userId);
        $this->updateScore($userId, $score);
    }

    /**
     * Get user article statistics.
     *
     * @param  User  $user  The user to get stats for
     * @return array{articles_count: int, total_views: int, total_likes: int, total_comments: int}
     */
    private function getUserArticleStats(User $user): array
    {
        /** @var string $userId */
        $userId = $user->id;

        return $this->articleRepository->getPublishedArticleStatsByAuthor($userId);
    }

    /**
     * Update expiration time for ranking key.
     */
    private function updateExpiration(): void
    {
        Redis::expire(self::RANKING_KEY, self::TTL_DAYS * 86400);
    }
}
