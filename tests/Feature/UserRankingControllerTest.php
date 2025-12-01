<?php

use App\Contracts\UserRankingServiceInterface;
use App\Models\Article;
use App\Models\Follower;
use App\Models\User;
use Illuminate\Support\Facades\Redis;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

beforeEach(function (): void {
    Redis::del('users:ranking:influence');
});

describe('GET /api/users/ranking', function (): void {
    it('returns top ranked users', function (): void {
        $users = User::factory()->count(5)->create();

        /** @var UserRankingServiceInterface $service */
        $service = app(UserRankingServiceInterface::class);

        foreach ($users as $index => $user) {
            $userId = (string) $user->id;
            $service->updateScore($userId, ($index + 1) * 100);
        }

        $response = getJson('/api/users/ranking?limit=3');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'rank',
                        'user_id',
                        'score',
                        'user',
                    ],
                ],
            ])
            ->assertJsonCount(3, 'data');
    });

    it('returns empty array when no users are ranked', function (): void {
        $response = getJson('/api/users/ranking');

        $response->assertStatus(200)
            ->assertJson(['data' => []]);
    });

    it('limits results to 100 maximum', function (): void {
        $response = getJson('/api/users/ranking?limit=200');

        $response->assertStatus(200);
    });

    it('defaults to 10 results when no limit specified', function (): void {
        $users = User::factory()->count(15)->create();

        /** @var UserRankingServiceInterface $service */
        $service = app(UserRankingServiceInterface::class);

        foreach ($users as $index => $user) {
            $service->updateScore((string) $user->id, $index + 1);
        }

        $response = getJson('/api/users/ranking');

        $response->assertStatus(200)
            ->assertJsonCount(10, 'data');
    });
});

describe('GET /api/users/ranking/statistics', function (): void {
    it('returns ranking statistics', function (): void {
        $users = User::factory()->count(3)->create();

        /** @var UserRankingServiceInterface $service */
        $service = app(UserRankingServiceInterface::class);

        foreach ($users as $index => $user) {
            $service->updateScore((string) $user->id, ($index + 1) * 50);
        }

        $response = getJson('/api/users/ranking/statistics');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'total_users',
                    'total_score',
                    'top_score',
                    'average_score',
                ],
            ]);

        $data = $response->json('data');
        expect($data['total_users'])->toBe(3)
            ->and((float) $data['total_score'])->toBe(300.0)
            ->and((float) $data['top_score'])->toBe(150.0)
            ->and((float) $data['average_score'])->toBe(100.0);
    });

    it('returns zero statistics when ranking is empty', function (): void {
        $response = getJson('/api/users/ranking/statistics');

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'total_users' => 0,
                    'total_score' => 0.0,
                    'top_score' => 0.0,
                    'average_score' => 0.0,
                ],
            ]);
    });
});

describe('POST /api/users/ranking/sync', function (): void {
    it('requires authentication', function (): void {
        $response = postJson('/api/users/ranking/sync');

        $response->assertStatus(401);
    });

    it('synchronizes ranking from database', function (): void {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $author = User::factory()->create();
        Article::factory()->count(2)->create([
            'author_id' => $author->id,
            'status' => 'published',
            'view_count' => 100,
            'like_count' => 10,
            'comment_count' => 5,
        ]);

        Follower::factory()->count(3)->create([
            'following_id' => $author->id,
        ]);

        $response = postJson('/api/users/ranking/sync');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Ranking de usuários sincronizado com sucesso.',
            ]);

        /** @var UserRankingServiceInterface $service */
        $service = app(UserRankingServiceInterface::class);
        $score = $service->getUserScore((string) $author->id);

        expect($score)->toBeGreaterThan(0);
    });
});

describe('GET /api/users/{user}/ranking', function (): void {
    it('requires authentication', function (): void {
        $user = User::factory()->create();

        $response = getJson("/api/users/{$user->id}/ranking");

        $response->assertStatus(401);
    });

    it('returns user ranking info', function (): void {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        /** @var UserRankingServiceInterface $service */
        $service = app(UserRankingServiceInterface::class);
        $service->updateScore((string) $user->id, 150.5);

        $response = getJson("/api/users/{$user->id}/ranking");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'user_id',
                    'rank',
                    'score',
                    'followers_count',
                    'articles_count',
                    'total_views',
                    'total_likes',
                    'total_comments',
                    'user',
                ],
                'breakdown',
            ]);
    });

    it('returns ranking info for non-ranked user', function (): void {
        $user = User::factory()->create();
        $targetUser = User::factory()->create();
        Sanctum::actingAs($user);

        $response = getJson("/api/users/{$targetUser->id}/ranking");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'user_id' => (string) $targetUser->id,
                    'rank' => null,
                    'score' => 0.0,
                ],
            ]);
    });

    it('includes influence breakdown', function (): void {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        Article::factory()->create([
            'author_id' => $user->id,
            'status' => 'published',
            'view_count' => 100,
            'like_count' => 10,
            'comment_count' => 5,
        ]);

        /** @var UserRankingServiceInterface $service */
        $service = app(UserRankingServiceInterface::class);
        $service->recalculateUser((string) $user->id);

        $response = getJson("/api/users/{$user->id}/ranking");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'breakdown' => [
                    'followers',
                    'views',
                    'likes',
                    'comments',
                    'articles',
                ],
            ]);

        $breakdown = $response->json('breakdown');
        expect($breakdown['views']['value'])->toBe(100)
            ->and($breakdown['likes']['value'])->toBe(10)
            ->and($breakdown['comments']['value'])->toBe(5)
            ->and($breakdown['articles']['value'])->toBe(1);
    });
});

describe('POST /api/users/{user}/ranking/recalculate', function (): void {
    it('requires authentication', function (): void {
        $user = User::factory()->create();

        $response = postJson("/api/users/{$user->id}/ranking/recalculate");

        $response->assertStatus(401);
    });

    it('recalculates user ranking', function (): void {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        Article::factory()->create([
            'author_id' => $user->id,
            'status' => 'published',
            'view_count' => 200,
            'like_count' => 20,
            'comment_count' => 10,
        ]);

        Follower::factory()->count(5)->create([
            'following_id' => $user->id,
        ]);

        $response = postJson("/api/users/{$user->id}/ranking/recalculate");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Ranking do usuário recalculado com sucesso.',
            ])
            ->assertJsonStructure([
                'data' => [
                    'user_id',
                    'rank',
                    'score',
                ],
            ]);

        /** @var UserRankingServiceInterface $service */
        $service = app(UserRankingServiceInterface::class);
        $score = $service->getUserScore((string) $user->id);

        expect($score)->toBe(139.5);
    });

    it('returns updated ranking data after recalculation', function (): void {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = postJson("/api/users/{$user->id}/ranking/recalculate");

        $response->assertStatus(200);
        $data = $response->json('data');

        expect($data['user_id'])->toBe((string) $user->id);
    });
});
