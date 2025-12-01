<?php

use App\Contracts\Neo4jRepositoryInterface;
use App\DTOs\RecommendationDTO;
use App\Enums\RecommendationType;
use App\Models\Article;
use App\Models\User;
use App\Services\RecommendationService;
use Illuminate\Support\Facades\Cache;

beforeEach(function (): void {
    $this->mockNeo4j = Mockery::mock(Neo4jRepositoryInterface::class);
    $this->service = new RecommendationService($this->mockNeo4j);
});

afterEach(function (): void {
    Mockery::close();
});

describe('RecommendationService', function (): void {
    describe('getRecommendedUsers', function (): void {
        it('returns recommended users from Neo4j', function (): void {
            $userId = 'user-123';
            $expectedUsers = collect([
                ['id' => 'user-1', 'name' => 'User 1', 'username' => 'user1', 'common_followers' => 5],
                ['id' => 'user-2', 'name' => 'User 2', 'username' => 'user2', 'common_followers' => 3],
            ]);

            $this->mockNeo4j->shouldReceive('isConnected')->andReturn(true);
            $this->mockNeo4j->shouldReceive('getUsersWithCommonFollowers')
                ->with($userId, 10)
                ->andReturn($expectedUsers);

            $result = $this->service->getRecommendedUsers($userId);

            expect($result)->toBeInstanceOf(RecommendationDTO::class)
                ->and($result->type)->toBe(RecommendationType::Users)
                ->and($result->totalCount)->toBe(2)
                ->and($result->forUserId)->toBe($userId)
                ->and($result->items->count())->toBe(2);
        });

        it('returns empty DTO when Neo4j is unavailable', function (): void {
            $userId = 'user-123';

            $this->mockNeo4j->shouldReceive('isConnected')->andReturn(false);

            $result = $this->service->getRecommendedUsers($userId);

            expect($result)->toBeInstanceOf(RecommendationDTO::class)
                ->and($result->isEmpty())->toBeTrue()
                ->and($result->metadata)->toHaveKey('empty', true);
        });

        it('respects custom limit', function (): void {
            $userId = 'user-123';

            $this->mockNeo4j->shouldReceive('isConnected')->andReturn(true);
            $this->mockNeo4j->shouldReceive('getUsersWithCommonFollowers')
                ->with($userId, 5)
                ->andReturn(collect());

            $result = $this->service->getRecommendedUsers($userId, 5);

            expect($result->type)->toBe(RecommendationType::Users);
        });
    });

    describe('getRecommendedArticles', function (): void {
        it('returns recommended articles from Neo4j', function (): void {
            $userId = 'user-123';
            $expectedArticles = collect([
                ['id' => 'article-1', 'title' => 'Article 1', 'slug' => 'article-1', 'relevance_score' => 10],
            ]);

            $this->mockNeo4j->shouldReceive('isConnected')->andReturn(true);
            $this->mockNeo4j->shouldReceive('getRecommendedArticlesForUser')
                ->with($userId, 10)
                ->andReturn($expectedArticles);

            $result = $this->service->getRecommendedArticles($userId);

            expect($result)->toBeInstanceOf(RecommendationDTO::class)
                ->and($result->type)->toBe(RecommendationType::Articles)
                ->and($result->totalCount)->toBe(1);
        });

        it('returns empty DTO when Neo4j is unavailable', function (): void {
            $userId = 'user-123';

            $this->mockNeo4j->shouldReceive('isConnected')->andReturn(false);

            $result = $this->service->getRecommendedArticles($userId);

            expect($result->isEmpty())->toBeTrue();
        });
    });

    describe('getRelatedArticles', function (): void {
        it('returns related articles from Neo4j', function (): void {
            $articleId = 'article-123';
            $expectedArticles = collect([
                ['id' => 'related-1', 'title' => 'Related 1', 'slug' => 'related-1', 'common_tags' => 3],
            ]);

            $this->mockNeo4j->shouldReceive('isConnected')->andReturn(true);
            $this->mockNeo4j->shouldReceive('getRelatedArticlesByTags')
                ->with($articleId, 10)
                ->andReturn($expectedArticles);

            $result = $this->service->getRelatedArticles($articleId);

            expect($result)->toBeInstanceOf(RecommendationDTO::class)
                ->and($result->type)->toBe(RecommendationType::RelatedArticles)
                ->and($result->forArticleId)->toBe($articleId);
        });

        it('returns empty DTO when Neo4j is unavailable', function (): void {
            Cache::flush();

            $articleId = 'article-unavailable-123';

            $this->mockNeo4j->shouldReceive('isConnected')->andReturn(false);

            $result = $this->service->getRelatedArticles($articleId);

            expect($result)->toBeInstanceOf(RecommendationDTO::class)
                ->and($result->isEmpty())->toBeTrue();
        });
    });

    describe('getRecommendedAuthors', function (): void {
        it('returns influential authors from Neo4j', function (): void {
            $expectedAuthors = collect([
                ['id' => 'author-1', 'name' => 'Author 1', 'followers' => 100, 'articles' => 50],
            ]);

            $this->mockNeo4j->shouldReceive('isConnected')->andReturn(true);
            $this->mockNeo4j->shouldReceive('getInfluentialAuthors')
                ->andReturn($expectedAuthors);

            $result = $this->service->getRecommendedAuthors();

            expect($result)->toBeInstanceOf(RecommendationDTO::class)
                ->and($result->type)->toBe(RecommendationType::Authors)
                ->and($result->totalCount)->toBe(1);
        });

        it('returns empty DTO when Neo4j is unavailable', function (): void {
            Cache::flush();

            $this->mockNeo4j->shouldReceive('isConnected')->andReturn(false);

            $result = $this->service->getRecommendedAuthors(7);

            expect($result)->toBeInstanceOf(RecommendationDTO::class)
                ->and($result->isEmpty())->toBeTrue();
        });
    });

    describe('getTopicsOfInterest', function (): void {
        it('returns topics of interest from Neo4j', function (): void {
            $userId = 'user-123';
            $expectedTopics = collect([
                ['name' => 'laravel', 'interactions' => 10, 'type' => 'tag'],
                ['name' => 'php', 'interactions' => 8, 'type' => 'tag'],
            ]);

            $this->mockNeo4j->shouldReceive('isConnected')->andReturn(true);
            $this->mockNeo4j->shouldReceive('getTopicsOfInterest')
                ->with($userId, 10)
                ->andReturn($expectedTopics);

            $result = $this->service->getTopicsOfInterest($userId);

            expect($result)->toBeInstanceOf(RecommendationDTO::class)
                ->and($result->type)->toBe(RecommendationType::Topics)
                ->and($result->totalCount)->toBe(2);
        });

        it('returns empty DTO when Neo4j is unavailable', function (): void {
            Cache::flush();

            $userId = 'user-unavailable-123';

            $this->mockNeo4j->shouldReceive('isConnected')->andReturn(false);

            $result = $this->service->getTopicsOfInterest($userId);

            expect($result)->toBeInstanceOf(RecommendationDTO::class)
                ->and($result->isEmpty())->toBeTrue();
        });
    });

    describe('isAvailable', function (): void {
        it('returns true when Neo4j is connected', function (): void {
            $this->mockNeo4j->shouldReceive('isConnected')->andReturn(true);

            expect($this->service->isAvailable())->toBeTrue();
        });

        it('returns false when Neo4j is not connected', function (): void {
            $this->mockNeo4j->shouldReceive('isConnected')->andReturn(false);

            expect($this->service->isAvailable())->toBeFalse();
        });
    });

    describe('getStatistics', function (): void {
        it('returns graph statistics from Neo4j', function (): void {
            $expectedStats = [
                'users' => 10,
                'articles' => 20,
                'follows' => 15,
                'likes' => 30,
                'tags' => 5,
                'categories' => 3,
            ];

            $this->mockNeo4j->shouldReceive('getStatistics')
                ->andReturn($expectedStats);

            $result = $this->service->getStatistics();

            expect($result)->toBe($expectedStats);
        });
    });

    describe('syncFromDatabase', function (): void {
        it('syncs all entities when Neo4j is available', function (): void {
            $this->mockNeo4j->shouldReceive('isConnected')->andReturn(true);
            $this->mockNeo4j->shouldReceive('syncUser')->andReturn();
            $this->mockNeo4j->shouldReceive('syncArticle')->andReturn();
            $this->mockNeo4j->shouldReceive('syncFollow')->andReturn();
            $this->mockNeo4j->shouldReceive('syncLike')->andReturn();

            $user = User::factory()->create();
            $article = Article::factory()->create([
                'author_id' => $user->id,
                'status' => 'published',
            ]);

            $result = $this->service->syncFromDatabase();

            expect($result)->toHaveKeys(['users', 'articles', 'follows', 'likes'])
                ->and($result['users'])->toBeGreaterThanOrEqual(1)
                ->and($result['articles'])->toBeGreaterThanOrEqual(1);
        });

        it('returns zeros when Neo4j is unavailable', function (): void {
            $this->mockNeo4j->shouldReceive('isConnected')->andReturn(false);

            $result = $this->service->syncFromDatabase();

            expect($result)->toBe([
                'users' => 0,
                'articles' => 0,
                'follows' => 0,
                'likes' => 0,
            ]);
        });
    });
});
