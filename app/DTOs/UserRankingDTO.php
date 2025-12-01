<?php

namespace App\DTOs;

/**
 * Data Transfer Object for User Ranking.
 *
 * Encapsulates user ranking data including influence metrics.
 */
class UserRankingDTO
{
    /**
     * Create a new User Ranking DTO instance.
     *
     * @param  string  $userId  The user ID
     * @param  int|null  $rank  The user's rank position (1-based)
     * @param  float  $score  The influence score
     * @param  int  $followersCount  Number of followers
     * @param  int  $articlesCount  Number of published articles
     * @param  int  $totalViews  Total article views
     * @param  int  $totalLikes  Total article likes
     * @param  int  $totalComments  Total article comments
     * @param  array<string, mixed>  $user  User profile data
     */
    public function __construct(
        public readonly string $userId,
        public readonly ?int $rank,
        public readonly float $score,
        public readonly int $followersCount = 0,
        public readonly int $articlesCount = 0,
        public readonly int $totalViews = 0,
        public readonly int $totalLikes = 0,
        public readonly int $totalComments = 0,
        /** @var array<string, mixed> */
        public readonly array $user = [],
    ) {}

    /**
     * Create DTO from array.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        /** @var string $userId */
        $userId = $data['user_id'] ?? '';

        /** @var int|null $rank */
        $rank = isset($data['rank']) ? (int) $data['rank'] : null;

        /** @var int|float|string $scoreRaw */
        $scoreRaw = $data['score'] ?? 0.0;
        $score = (float) $scoreRaw;

        /** @var int $followersCount */
        $followersCount = (int) ($data['followers_count'] ?? 0);

        /** @var int $articlesCount */
        $articlesCount = (int) ($data['articles_count'] ?? 0);

        /** @var int $totalViews */
        $totalViews = (int) ($data['total_views'] ?? 0);

        /** @var int $totalLikes */
        $totalLikes = (int) ($data['total_likes'] ?? 0);

        /** @var int $totalComments */
        $totalComments = (int) ($data['total_comments'] ?? 0);

        /** @var array<string, mixed> $user */
        $user = $data['user'] ?? [];

        return new self(
            userId: $userId,
            rank: $rank,
            score: $score,
            followersCount: $followersCount,
            articlesCount: $articlesCount,
            totalViews: $totalViews,
            totalLikes: $totalLikes,
            totalComments: $totalComments,
            user: $user,
        );
    }

    /**
     * Convert DTO to array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'user_id' => $this->userId,
            'rank' => $this->rank,
            'score' => $this->score,
            'followers_count' => $this->followersCount,
            'articles_count' => $this->articlesCount,
            'total_views' => $this->totalViews,
            'total_likes' => $this->totalLikes,
            'total_comments' => $this->totalComments,
            'user' => $this->user,
        ];
    }

    /**
     * Get breakdown of influence factors.
     *
     * @return array<string, array{value: int, weight: float, contribution: float}>
     */
    public function getInfluenceBreakdown(): array
    {
        return [
            'followers' => [
                'value' => $this->followersCount,
                'weight' => 2.0,
                'contribution' => $this->followersCount * 2.0,
            ],
            'views' => [
                'value' => $this->totalViews,
                'weight' => 0.5,
                'contribution' => $this->totalViews * 0.5,
            ],
            'likes' => [
                'value' => $this->totalLikes,
                'weight' => 1.0,
                'contribution' => $this->totalLikes * 1.0,
            ],
            'comments' => [
                'value' => $this->totalComments,
                'weight' => 0.8,
                'contribution' => $this->totalComments * 0.8,
            ],
            'articles' => [
                'value' => $this->articlesCount,
                'weight' => 1.5,
                'contribution' => $this->articlesCount * 1.5,
            ],
        ];
    }
}
