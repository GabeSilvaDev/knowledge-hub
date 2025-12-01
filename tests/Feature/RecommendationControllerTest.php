<?php

use App\Contracts\Neo4jRepositoryInterface;
use App\Models\Article;
use App\Models\Follower;
use App\Models\Like;
use App\Models\User;
use Illuminate\Http\JsonResponse;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;
use function Pest\Laravel\mock;
use function Pest\Laravel\postJson;

beforeEach(function (): void {
    Follower::query()->delete();
    Like::query()->delete();
    Article::query()->delete();
    User::query()->delete();

    $this->user = User::factory()->create();

    $this->mockNeo4j = mock(Neo4jRepositoryInterface::class);
    $this->mockNeo4j->shouldReceive('isConnected')->andReturn(true);
    $this->mockNeo4j->shouldReceive('syncUser')->andReturn();
    $this->mockNeo4j->shouldReceive('syncArticle')->andReturn();
    $this->mockNeo4j->shouldReceive('syncFollow')->andReturn();
    $this->mockNeo4j->shouldReceive('syncLike')->andReturn();
    $this->mockNeo4j->shouldReceive('deleteUser')->andReturn();
    $this->mockNeo4j->shouldReceive('deleteArticle')->andReturn();
    $this->mockNeo4j->shouldReceive('deleteFollow')->andReturn();
    $this->mockNeo4j->shouldReceive('deleteLike')->andReturn();
});

describe('RecommendationController Feature Tests', function (): void {
    describe('GET /api/recommendations/users', function (): void {
        it('requires authentication', function (): void {
            $response = getJson('/api/recommendations/users');

            $response->assertStatus(JsonResponse::HTTP_UNAUTHORIZED);
        });

        it('returns recommended users for authenticated user', function (): void {
            $this->mockNeo4j->shouldReceive('getUsersWithCommonFollowers')
                ->andReturn(collect([
                    ['id' => 'user-1', 'name' => 'Test User', 'username' => 'testuser', 'common_followers' => 5],
                ]));

            actingAs($this->user);

            $response = getJson('/api/recommendations/users');

            $response->assertStatus(JsonResponse::HTTP_OK)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'type',
                        'items',
                        'total_count',
                        'for_user_id',
                    ],
                ]);
        });

        it('returns empty recommendations when none available', function (): void {
            $this->mockNeo4j->shouldReceive('getUsersWithCommonFollowers')
                ->andReturn(collect());

            actingAs($this->user);

            $response = getJson('/api/recommendations/users');

            $response->assertStatus(JsonResponse::HTTP_OK)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'type' => 'users',
                        'total_count' => 0,
                    ],
                ]);
        });

        it('respects limit parameter', function (): void {
            $this->mockNeo4j->shouldReceive('getUsersWithCommonFollowers')
                ->withArgs(fn($userId, $limit): bool => $limit === 5)
                ->andReturn(collect());

            actingAs($this->user);

            $response = getJson('/api/recommendations/users?limit=5');

            $response->assertStatus(JsonResponse::HTTP_OK);
        });
    });

    describe('GET /api/recommendations/articles', function (): void {
        it('requires authentication', function (): void {
            $response = getJson('/api/recommendations/articles');

            $response->assertStatus(JsonResponse::HTTP_UNAUTHORIZED);
        });

        it('returns recommended articles for authenticated user', function (): void {
            $this->mockNeo4j->shouldReceive('getRecommendedArticlesForUser')
                ->andReturn(collect([
                    [
                        'id' => 'article-1',
                        'title' => 'Test Article',
                        'slug' => 'test-article',
                        'author_id' => 'author-1',
                        'relevance_score' => 5,
                    ],
                ]));

            actingAs($this->user);

            $response = getJson('/api/recommendations/articles');

            $response->assertStatus(JsonResponse::HTTP_OK)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'type',
                        'items',
                        'total_count',
                    ],
                ]);
        });

        it('returns empty message when no recommended articles available', function (): void {
            $this->mockNeo4j->shouldReceive('getRecommendedArticlesForUser')
                ->andReturn(collect());

            actingAs($this->user);

            $response = getJson('/api/recommendations/articles');

            $response->assertStatus(JsonResponse::HTTP_OK)
                ->assertJson([
                    'success' => true,
                    'message' => 'No article recommendations available at the moment.',
                    'data' => [
                        'type' => 'articles',
                        'total_count' => 0,
                    ],
                ]);
        });
    });

    describe('GET /api/articles/{articleId}/related', function (): void {
        it('returns related articles for a given article', function (): void {
            $article = Article::factory()->create([
                'author_id' => $this->user->id,
                'status' => 'published',
            ]);

            $this->mockNeo4j->shouldReceive('getRelatedArticlesByTags')
                ->andReturn(collect([
                    [
                        'id' => 'related-1',
                        'title' => 'Related Article',
                        'slug' => 'related-article',
                        'author_id' => 'author-1',
                        'common_tags' => 3,
                    ],
                ]));

            $response = getJson("/api/articles/{$article->id}/related");

            $response->assertStatus(JsonResponse::HTTP_OK)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'type',
                        'items',
                        'total_count',
                        'for_article_id',
                    ],
                ]);
        });

        it('returns empty when no related articles found', function (): void {
            $article = Article::factory()->create([
                'author_id' => $this->user->id,
                'status' => 'published',
            ]);

            $this->mockNeo4j->shouldReceive('getRelatedArticlesByTags')
                ->andReturn(collect());

            $response = getJson("/api/articles/{$article->id}/related");

            $response->assertStatus(JsonResponse::HTTP_OK)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'type' => 'related_articles',
                        'total_count' => 0,
                    ],
                ]);
        });
    });

    describe('GET /api/recommendations/authors', function (): void {
        it('returns influential authors', function (): void {
            $this->mockNeo4j->shouldReceive('getInfluentialAuthors')
                ->andReturn(collect([
                    [
                        'id' => 'author-1',
                        'name' => 'Famous Author',
                        'username' => 'famousauthor',
                        'followers' => 100,
                        'articles' => 50,
                    ],
                ]));

            $response = getJson('/api/recommendations/authors');

            $response->assertStatus(JsonResponse::HTTP_OK)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'type',
                        'items',
                        'total_count',
                    ],
                ]);
        });

        it('is publicly accessible', function (): void {
            $this->mockNeo4j->shouldReceive('getInfluentialAuthors')
                ->andReturn(collect());

            $response = getJson('/api/recommendations/authors');

            $response->assertStatus(JsonResponse::HTTP_OK);
        });
    });

    describe('GET /api/recommendations/topics', function (): void {
        it('requires authentication', function (): void {
            $response = getJson('/api/recommendations/topics');

            $response->assertStatus(JsonResponse::HTTP_UNAUTHORIZED);
        });

        it('returns topics of interest for authenticated user', function (): void {
            $this->mockNeo4j->shouldReceive('getTopicsOfInterest')
                ->andReturn(collect([
                    ['name' => 'laravel', 'interactions' => 10, 'type' => 'tag'],
                    ['name' => 'php', 'interactions' => 8, 'type' => 'tag'],
                ]));

            actingAs($this->user);

            $response = getJson('/api/recommendations/topics');

            $response->assertStatus(JsonResponse::HTTP_OK)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'type',
                        'items',
                        'total_count',
                    ],
                ]);
        });

        it('returns empty message when no topics of interest found', function (): void {
            $this->mockNeo4j->shouldReceive('getTopicsOfInterest')
                ->andReturn(collect());

            actingAs($this->user);

            $response = getJson('/api/recommendations/topics');

            $response->assertStatus(JsonResponse::HTTP_OK)
                ->assertJson([
                    'success' => true,
                    'message' => 'No topics of interest identified yet.',
                    'data' => [
                        'type' => 'topics',
                        'total_count' => 0,
                    ],
                ]);
        });
    });

    describe('POST /api/recommendations/sync', function (): void {
        it('requires authentication', function (): void {
            $response = postJson('/api/recommendations/sync');

            $response->assertStatus(JsonResponse::HTTP_UNAUTHORIZED);
        });

        it('syncs data from MongoDB to Neo4j', function (): void {
            $this->mockNeo4j->shouldReceive('getStatistics')
                ->andReturn([
                    'users' => 10,
                    'articles' => 20,
                    'follows' => 15,
                    'likes' => 30,
                    'tags' => 5,
                    'categories' => 3,
                ]);

            actingAs($this->user);

            $response = postJson('/api/recommendations/sync');

            $response->assertStatus(JsonResponse::HTTP_OK)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'synced',
                        'neo4j_available',
                    ],
                ]);
        });
    });

    describe('GET /api/recommendations/statistics', function (): void {
        it('returns Neo4j graph statistics', function (): void {
            $this->mockNeo4j->shouldReceive('getStatistics')
                ->andReturn([
                    'users' => 10,
                    'articles' => 20,
                    'follows' => 15,
                    'likes' => 30,
                    'tags' => 5,
                    'categories' => 3,
                ]);

            $response = getJson('/api/recommendations/statistics');

            $response->assertStatus(JsonResponse::HTTP_OK)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'neo4j_available',
                        'statistics',
                    ],
                ]);
        });

        it('is publicly accessible', function (): void {
            $this->mockNeo4j->shouldReceive('getStatistics')
                ->andReturn([
                    'users' => 0,
                    'articles' => 0,
                    'follows' => 0,
                    'likes' => 0,
                    'tags' => 0,
                    'categories' => 0,
                ]);

            $response = getJson('/api/recommendations/statistics');

            $response->assertStatus(JsonResponse::HTTP_OK);
        });
    });
});

describe('Neo4j Unavailable Scenarios', function (): void {
    beforeEach(function (): void {
        $this->mockNeo4j = mock(Neo4jRepositoryInterface::class);
        $this->mockNeo4j->shouldReceive('isConnected')->andReturn(false);
        $this->mockNeo4j->shouldReceive('syncUser')->andReturn();
        $this->mockNeo4j->shouldReceive('syncArticle')->andReturn();
        $this->mockNeo4j->shouldReceive('syncFollow')->andReturn();
        $this->mockNeo4j->shouldReceive('syncLike')->andReturn();
    });

    it('returns empty recommendations when Neo4j is unavailable', function (): void {
        $this->mockNeo4j->shouldReceive('getUsersWithCommonFollowers')->never();

        actingAs($this->user);

        $response = getJson('/api/recommendations/users');

        $response->assertStatus(JsonResponse::HTTP_OK)
            ->assertJson([
                'success' => true,
                'data' => [
                    'total_count' => 0,
                    'metadata' => [
                        'empty' => true,
                    ],
                ],
            ]);
    });

    it('returns neo4j_available false in statistics', function (): void {
        $this->mockNeo4j->shouldReceive('getStatistics')
            ->andReturn([
                'users' => 0,
                'articles' => 0,
                'follows' => 0,
                'likes' => 0,
                'tags' => 0,
                'categories' => 0,
            ]);

        $response = getJson('/api/recommendations/statistics');

        $response->assertStatus(JsonResponse::HTTP_OK)
            ->assertJson([
                'success' => true,
                'data' => [
                    'neo4j_available' => false,
                ],
            ]);
    });
});
