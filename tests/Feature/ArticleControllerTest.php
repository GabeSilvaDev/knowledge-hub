<?php

use App\Models\Article;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\deleteJson;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\putJson;

const ARTICLES_ENDPOINT = '/api/articles';

beforeEach(function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    test()->user = $user;
});

describe('GET /api/articles', function (): void {
    it('returns paginated list of articles', function (): void {
        Article::factory()->count(5)->create();

        $response = getJson(ARTICLES_ENDPOINT);

        $response->assertStatus(JsonResponse::HTTP_OK)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'slug',
                        'content',
                        'excerpt',
                        'author_id',
                        'status',
                        'type',
                    ],
                ],
                'current_page',
                'per_page',
                'total',
            ]);
    });
});

describe('POST /api/articles', function (): void {
    it('creates article successfully with valid data', function (): void {
        $articleData = [
            'title' => 'My Test Article Title',
            'content' => 'This is the content for the article test.',
            'excerpt' => 'Article excerpt here',
            'status' => 'draft',
            'type' => 'post',
            'tags' => ['php', 'laravel'],
            'categories' => ['programming'],
        ];

        $response = postJson(ARTICLES_ENDPOINT, $articleData);

        $response->assertStatus(Response::HTTP_CREATED)
            ->assertJson([
                'message' => 'Article created successfully.',
            ])
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'title',
                    'slug',
                    'content',
                    'excerpt',
                    'author_id',
                    'status',
                    'type',
                ],
            ]);

        expect($response->json('data.title'))->toBe('My Test Article Title')
            ->and($response->json('data.author_id'))->toBe(test()->user->_id);
    });

    it('sets authenticated user as article author on create', function (): void {
        $articleData = [
            'title' => 'Author Test Title',
            'content' => 'Author test content goes here',
            'excerpt' => 'Author test excerpt',
            'status' => 'draft',
            'type' => 'post',
        ];

        $response = postJson(ARTICLES_ENDPOINT, $articleData);

        $response->assertStatus(Response::HTTP_CREATED);
        expect($response->json('data.author_id'))->toBe(test()->user->_id);
    });
});

describe('GET /api/articles/{article}', function (): void {
    it('returns article with author relationship loaded', function (): void {
        /** @var Article $article */
        $article = Article::factory()->create([
            'author_id' => test()->user->_id,
        ]);

        $response = getJson(ARTICLES_ENDPOINT . "/{$article->_id}");

        $response->assertStatus(JsonResponse::HTTP_OK)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'slug',
                    'content',
                    'author',
                ],
            ])
            ->assertJsonPath('data.id', $article->_id);
    });

    it('returns not found error when article does not exist', function (): void {
        $invalidId = '507f1f77bcf86cd799439011';

        getJson(ARTICLES_ENDPOINT . "/{$invalidId}")
            ->assertStatus(JsonResponse::HTTP_NOT_FOUND)
            ->assertJson([
                'message' => 'The requested resource (Article) was not found.',
                'error' => 'Resource not found',
            ]);
    });
});

describe('PUT /api/articles/{article}', function (): void {
    it('updates article successfully when data is valid', function (): void {
        /** @var Article $article */
        $article = Article::factory()->create([
            'author_id' => test()->user->_id,
            'title' => 'Original Article Title',
        ]);

        $updateData = [
            'title' => 'Updated Article Title',
            'content' => 'Updated article content',
            'excerpt' => 'Updated article excerpt',
            'status' => 'published',
        ];

        $response = putJson(ARTICLES_ENDPOINT . "/{$article->_id}", $updateData);

        $response->assertStatus(JsonResponse::HTTP_OK)
            ->assertJson([
                'message' => 'Article updated successfully.',
            ])
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'title',
                    'content',
                ],
            ]);

        expect($response->json('data.title'))->toBe('Updated Article Title');
    });
});

describe('DELETE /api/articles/{article}', function (): void {
    it('deletes article successfully', function (): void {
        /** @var Article $article */
        $article = Article::factory()->create([
            'author_id' => test()->user->_id,
        ]);

        $response = deleteJson(ARTICLES_ENDPOINT . "/{$article->_id}");

        $response->assertStatus(JsonResponse::HTTP_OK)
            ->assertJson([
                'message' => 'Article deleted successfully.',
            ]);
    });

    it('returns not found error when deleting nonexistent article', function (): void {
        $invalidId = '507f1f77bcf86cd799439000';

        deleteJson(ARTICLES_ENDPOINT . "/{$invalidId}")
            ->assertStatus(JsonResponse::HTTP_NOT_FOUND)
            ->assertJson([
                'message' => 'The requested resource (Article) was not found.',
                'error' => 'Resource not found',
            ]);
    });

    it('uses soft delete instead of hard delete', function (): void {
        /** @var Article $article */
        $article = Article::factory()->create();

        deleteJson(ARTICLES_ENDPOINT . "/{$article->_id}")
            ->assertStatus(JsonResponse::HTTP_OK);

        $article->refresh();
        expect($article->deleted_at)->not->toBeNull();
    });
});

describe('ArticleController integration flows', function (): void {
    it('creates and retrieves article in full flow', function (): void {
        $createResponse = postJson(ARTICLES_ENDPOINT, [
            'title' => 'Full Flow Test Article',
            'content' => 'Full flow test content',
            'excerpt' => 'Full flow test excerpt',
            'status' => 'draft',
            'type' => 'post',
        ]);

        $createResponse->assertStatus(Response::HTTP_CREATED);
        $articleId = $createResponse->json('data.id');

        $getResponse = getJson(ARTICLES_ENDPOINT . "/{$articleId}");
        $getResponse->assertStatus(JsonResponse::HTTP_OK)
            ->assertJsonPath('data.title', 'Full Flow Test Article');
    });

    it('creates updates and deletes article in complete lifecycle', function (): void {
        $createResponse = postJson(ARTICLES_ENDPOINT, [
            'title' => 'Lifecycle Article',
            'content' => 'Lifecycle content',
            'excerpt' => 'Lifecycle excerpt',
            'status' => 'draft',
            'type' => 'post',
        ]);

        $articleId = $createResponse->json('data.id');

        $updateResponse = putJson(ARTICLES_ENDPOINT . "/{$articleId}", [
            'title' => 'Updated Lifecycle Article',
            'content' => 'Updated lifecycle content',
            'excerpt' => 'Updated lifecycle excerpt',
            'status' => 'published',
        ]);

        $updateResponse->assertStatus(JsonResponse::HTTP_OK)
            ->assertJsonPath('data.title', 'Updated Lifecycle Article');

        $deleteResponse = deleteJson(ARTICLES_ENDPOINT . "/{$articleId}");
        $deleteResponse->assertStatus(JsonResponse::HTTP_OK);

        getJson(ARTICLES_ENDPOINT . "/{$articleId}")
            ->assertStatus(JsonResponse::HTTP_NOT_FOUND);
    });
});

describe('GET /api/articles/popular', function (): void {
    it('returns popular articles', function (): void {
        Article::factory()->count(5)->create([
            'status' => 'published',
            'view_count' => 100,
            'published_at' => now()->subDays(5),
        ]);

        $response = getJson('/api/articles/popular');

        $response->assertStatus(JsonResponse::HTTP_OK)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'slug',
                        'view_count',
                    ],
                ],
            ]);
    });

    it('respects limit query parameter', function (): void {
        Article::factory()->count(20)->create([
            'status' => 'published',
            'published_at' => now()->subDays(5),
        ]);

        $response = getJson('/api/articles/popular?limit=5');

        $response->assertStatus(JsonResponse::HTTP_OK);
        expect($response->json('data'))->toHaveCount(5);
    });

    it('respects days query parameter', function (): void {
        Article::truncate();

        Article::factory()->create([
            'status' => 'published',
            'view_count' => 100,
            'published_at' => now()->subDays(5),
        ]);

        Article::factory()->create([
            'status' => 'published',
            'view_count' => 200,
            'published_at' => now()->subDays(40),
        ]);

        $response = getJson('/api/articles/popular?days=30');

        $response->assertStatus(JsonResponse::HTTP_OK);
        expect($response->json('data'))->toHaveCount(1);
    });
});
