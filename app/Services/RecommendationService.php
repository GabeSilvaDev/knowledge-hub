<?php

namespace App\Services;

use App\Contracts\Neo4jRepositoryInterface;
use App\Contracts\RecommendationServiceInterface;
use App\Contracts\SyncRepositoryInterface;
use App\DTOs\RecommendationDTO;
use App\Enums\RecommendationType;
use Illuminate\Support\Facades\Cache;

/**
 * Recommendation Service.
 *
 * Handles business logic for recommendations using Neo4j graph database.
 */
final readonly class RecommendationService implements RecommendationServiceInterface
{
    /**
     * Initialize the Recommendation Service.
     *
     * @param  Neo4jRepositoryInterface  $neo4jRepository  Repository for Neo4j graph operations
     * @param  SyncRepositoryInterface  $syncRepository  Repository for sync data access
     */
    public function __construct(
        private Neo4jRepositoryInterface $neo4jRepository,
        private SyncRepositoryInterface $syncRepository,
    ) {}

    /**
     * Get recommended users for a specific user.
     *
     * @param  string  $userId  The user ID to get recommendations for
     * @param  int  $limit  The maximum number of recommendations
     * @return RecommendationDTO The recommended users data transfer object
     */
    public function getRecommendedUsers(string $userId, int $limit = 10): RecommendationDTO
    {
        /** @var int $maxLimit */
        $maxLimit = config('neo4j.recommendations.max_users', 10);
        $limit = min($limit, $maxLimit);

        /** @var int $cacheTtl */
        $cacheTtl = config('neo4j.recommendations.cache_ttl', 3600);

        $cacheKey = "recommendations:users:{$userId}:{$limit}";

        /** @var RecommendationDTO $dto */
        $dto = Cache::remember($cacheKey, $cacheTtl, function () use ($userId, $limit): RecommendationDTO {
            if (! $this->isAvailable()) {
                return RecommendationDTO::empty(RecommendationType::Users, $userId);
            }

            $users = $this->neo4jRepository->getUsersWithCommonFollowers($userId, $limit);

            return new RecommendationDTO(
                type: RecommendationType::Users,
                items: $users,
                totalCount: $users->count(),
                forUserId: $userId,
                metadata: [
                    'algorithm' => 'common_followers',
                    'generated_at' => now()->toIso8601String(),
                ],
            );
        });

        return $dto;
    }

    /**
     * Get recommended articles for a specific user.
     *
     * @param  string  $userId  The user ID to get recommendations for
     * @param  int  $limit  The maximum number of recommendations
     * @return RecommendationDTO The recommended articles data transfer object
     */
    public function getRecommendedArticles(string $userId, int $limit = 10): RecommendationDTO
    {
        /** @var int $maxLimit */
        $maxLimit = config('neo4j.recommendations.max_articles', 10);
        $limit = min($limit, $maxLimit);

        /** @var int $cacheTtl */
        $cacheTtl = config('neo4j.recommendations.cache_ttl', 3600);

        $cacheKey = "recommendations:articles:{$userId}:{$limit}";

        /** @var RecommendationDTO $dto */
        $dto = Cache::remember($cacheKey, $cacheTtl, function () use ($userId, $limit): RecommendationDTO {
            if (! $this->isAvailable()) {
                return RecommendationDTO::empty(RecommendationType::Articles, $userId);
            }

            $articles = $this->neo4jRepository->getRecommendedArticlesForUser($userId, $limit);

            return new RecommendationDTO(
                type: RecommendationType::Articles,
                items: $articles,
                totalCount: $articles->count(),
                forUserId: $userId,
                metadata: [
                    'algorithm' => 'tags_and_categories',
                    'generated_at' => now()->toIso8601String(),
                ],
            );
        });

        return $dto;
    }

    /**
     * Get related articles for a specific article.
     *
     * @param  string  $articleId  The article ID to get related articles for
     * @param  int  $limit  The maximum number of recommendations
     * @return RecommendationDTO The related articles data transfer object
     */
    public function getRelatedArticles(string $articleId, int $limit = 10): RecommendationDTO
    {
        /** @var int $maxLimit */
        $maxLimit = config('neo4j.recommendations.max_articles', 10);
        $limit = min($limit, $maxLimit);

        /** @var int $cacheTtl */
        $cacheTtl = config('neo4j.recommendations.cache_ttl', 3600);

        $cacheKey = "recommendations:related:{$articleId}:{$limit}";

        /** @var RecommendationDTO $dto */
        $dto = Cache::remember($cacheKey, $cacheTtl, function () use ($articleId, $limit): RecommendationDTO {
            if (! $this->isAvailable()) {
                return RecommendationDTO::empty(RecommendationType::RelatedArticles, null, $articleId);
            }

            $articles = $this->neo4jRepository->getRelatedArticlesByTags($articleId, $limit);

            return new RecommendationDTO(
                type: RecommendationType::RelatedArticles,
                items: $articles,
                totalCount: $articles->count(),
                forArticleId: $articleId,
                metadata: [
                    'algorithm' => 'common_tags_and_categories',
                    'generated_at' => now()->toIso8601String(),
                ],
            );
        });

        return $dto;
    }

    /**
     * Get recommended authors.
     *
     * @param  int  $limit  The maximum number of recommendations
     * @return RecommendationDTO The recommended authors data transfer object
     */
    public function getRecommendedAuthors(int $limit = 10): RecommendationDTO
    {
        /** @var int $maxLimit */
        $maxLimit = config('neo4j.recommendations.max_authors', 10);
        $limit = min($limit, $maxLimit);

        /** @var int $minFollowers */
        $minFollowers = config('neo4j.recommendations.min_followers_for_influential', 5);

        /** @var int $cacheTtl */
        $cacheTtl = config('neo4j.recommendations.cache_ttl', 3600);

        $cacheKey = "recommendations:authors:{$limit}:{$minFollowers}";

        /** @var RecommendationDTO $dto */
        $dto = Cache::remember($cacheKey, $cacheTtl, function () use ($minFollowers, $limit): RecommendationDTO {
            if (! $this->isAvailable()) {
                return RecommendationDTO::empty(RecommendationType::Authors);
            }

            $authors = $this->neo4jRepository->getInfluentialAuthors($minFollowers, $limit);

            return new RecommendationDTO(
                type: RecommendationType::Authors,
                items: $authors,
                totalCount: $authors->count(),
                metadata: [
                    'algorithm' => 'follower_count',
                    'min_followers' => $minFollowers,
                    'generated_at' => now()->toIso8601String(),
                ],
            );
        });

        return $dto;
    }

    /**
     * Get topics of interest for a user.
     *
     * @param  string  $userId  The user ID to get topics for
     * @param  int  $limit  The maximum number of topics
     * @return RecommendationDTO The topics data transfer object
     */
    public function getTopicsOfInterest(string $userId, int $limit = 10): RecommendationDTO
    {
        /** @var int $maxLimit */
        $maxLimit = config('neo4j.recommendations.max_topics', 10);
        $limit = min($limit, $maxLimit);

        /** @var int $cacheTtl */
        $cacheTtl = config('neo4j.recommendations.cache_ttl', 3600);

        $cacheKey = "recommendations:topics:{$userId}:{$limit}";

        /** @var RecommendationDTO $dto */
        $dto = Cache::remember($cacheKey, $cacheTtl, function () use ($userId, $limit): RecommendationDTO {
            if (! $this->isAvailable()) {
                return RecommendationDTO::empty(RecommendationType::Topics, $userId);
            }

            $topics = $this->neo4jRepository->getTopicsOfInterest($userId, $limit);

            return new RecommendationDTO(
                type: RecommendationType::Topics,
                items: $topics,
                totalCount: $topics->count(),
                forUserId: $userId,
                metadata: [
                    'algorithm' => 'likes_interaction',
                    'generated_at' => now()->toIso8601String(),
                ],
            );
        });

        return $dto;
    }

    /**
     * Sync all data from MongoDB to Neo4j.
     *
     * @return array<string, int>
     */
    public function syncFromDatabase(): array
    {
        $stats = [
            'users' => 0,
            'articles' => 0,
            'follows' => 0,
            'likes' => 0,
        ];

        if (! $this->isAvailable()) {
            return $stats;
        }

        foreach ($this->syncRepository->getAllUsersForSync() as $userData) {
            $this->neo4jRepository->syncUser($userData);
            $stats['users']++;
        }

        foreach ($this->syncRepository->getAllPublishedArticlesForSync() as $articleData) {
            $this->neo4jRepository->syncArticle($articleData);
            $stats['articles']++;
        }

        foreach ($this->syncRepository->getAllFollowersForSync() as $followerData) {
            $this->neo4jRepository->syncFollow(
                $followerData['follower_id'],
                $followerData['following_id']
            );
            $stats['follows']++;
        }

        foreach ($this->syncRepository->getAllLikesForSync() as $likeData) {
            $this->neo4jRepository->syncLike(
                $likeData['user_id'],
                $likeData['article_id']
            );
            $stats['likes']++;
        }

        return $stats;
    }

    /**
     * Check if Neo4j is connected and working.
     *
     * @return bool True if Neo4j is available
     */
    public function isAvailable(): bool
    {
        return $this->neo4jRepository->isConnected();
    }

    /**
     * Get statistics about the recommendation graph.
     *
     * @return array<string, int>
     */
    public function getStatistics(): array
    {
        return $this->neo4jRepository->getStatistics();
    }
}
