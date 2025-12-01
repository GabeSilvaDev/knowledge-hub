<?php

namespace App\Services;

use App\Contracts\Neo4jRepositoryInterface;
use App\Contracts\RecommendationServiceInterface;
use App\DTOs\RecommendationDTO;
use App\Enums\RecommendationType;
use App\Models\Article;
use App\Models\Follower;
use App\Models\Like;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

/**
 * Recommendation Service.
 *
 * Handles business logic for recommendations using Neo4j graph database.
 */
final readonly class RecommendationService implements RecommendationServiceInterface
{
    public function __construct(
        private Neo4jRepositoryInterface $neo4jRepository,
    ) {}

    /**
     * Get recommended users for a specific user.
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

        User::query()->cursor()->each(function (User $user) use (&$stats): void {
            $this->neo4jRepository->syncUser([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'username' => $user->username,
            ]);
            $stats['users']++;
        });

        Article::query()
            ->where('status', 'published')
            ->cursor()
            ->each(function (Article $article) use (&$stats): void {
                $this->neo4jRepository->syncArticle([
                    'id' => $article->id,
                    'title' => $article->title,
                    'slug' => $article->slug,
                    'status' => $article->status,
                    'author_id' => $article->author_id,
                    'view_count' => $article->view_count,
                    'like_count' => $article->like_count,
                    'tags' => $article->tags ?? [],
                    'categories' => $article->categories ?? [],
                ]);
                $stats['articles']++;
            });

        Follower::query()->cursor()->each(function (Follower $follower) use (&$stats): void {
            $this->neo4jRepository->syncFollow(
                (string) $follower->follower_id,
                (string) $follower->following_id
            );
            $stats['follows']++;
        });

        Like::query()->cursor()->each(function (Like $like) use (&$stats): void {
            $this->neo4jRepository->syncLike(
                (string) $like->user_id,
                (string) $like->article_id
            );
            $stats['likes']++;
        });

        return $stats;
    }

    /**
     * Check if Neo4j is connected and working.
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
