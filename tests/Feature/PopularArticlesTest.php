<?php

use App\Models\Article;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

use function Pest\Laravel\getJson;

describe('GET /api/articles/popular', function (): void {
    beforeEach(function (): void {
        Cache::flush();
        Article::truncate();
    });

    it('returns popular articles based on view count', function (): void {
        Article::factory()->create([
            'status' => 'published',
            'view_count' => 100,
            'published_at' => now()->subDays(5),
        ]);

        Article::factory()->create([
            'status' => 'published',
            'view_count' => 200,
            'published_at' => now()->subDays(3),
        ]);

        Article::factory()->create([
            'status' => 'published',
            'view_count' => 50,
            'published_at' => now()->subDays(1),
        ]);

        $response = getJson('/api/articles/popular');

        $response->assertStatus(JsonResponse::HTTP_OK)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'view_count',
                        'status',
                    ],
                ],
            ]);

        $data = $response->json('data');
        expect($data)->toHaveCount(3)
            ->and($data[0]['view_count'])->toBe(200)
            ->and($data[1]['view_count'])->toBe(100)
            ->and($data[2]['view_count'])->toBe(50);
    });

    it('respects limit parameter', function (): void {
        Article::factory()->count(15)->create([
            'status' => 'published',
            'published_at' => now()->subDays(5),
        ]);

        $response = getJson('/api/articles/popular?limit=5');

        $response->assertStatus(JsonResponse::HTTP_OK);

        $data = $response->json('data');
        expect($data)->toHaveCount(5);
    });

    it('respects days parameter', function (): void {
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

        $data = $response->json('data');
        expect($data)->toHaveCount(1)
            ->and($data[0]['view_count'])->toBe(100);
    });

    it('only returns published articles', function (): void {
        Article::factory()->create([
            'status' => 'draft',
            'view_count' => 300,
            'published_at' => now()->subDays(5),
        ]);

        Article::factory()->create([
            'status' => 'published',
            'view_count' => 100,
            'published_at' => now()->subDays(3),
        ]);

        $response = getJson('/api/articles/popular');

        $response->assertStatus(JsonResponse::HTTP_OK);

        $data = $response->json('data');
        expect($data)->toHaveCount(1)
            ->and($data[0]['view_count'])->toBe(100);
    });

    it('caches popular articles', function (): void {
        Article::factory()->create([
            'status' => 'published',
            'view_count' => 100,
            'published_at' => now()->subDays(5),
        ]);

        $response1 = getJson('/api/articles/popular?limit=10&days=30');
        $response1->assertStatus(JsonResponse::HTTP_OK);

        $cacheKey = 'popular_articles:days:30:limit:10';
        expect(Cache::has($cacheKey))->toBeTrue();

        $response2 = getJson('/api/articles/popular?limit=10&days=30');

        expect(Cache::has($cacheKey))->toBeTrue();
    });

    it('invalidates cache when article is created', function (): void {
        Article::factory()->create([
            'status' => 'published',
            'view_count' => 100,
            'published_at' => now()->subDays(5),
        ]);

        getJson('/api/articles/popular?limit=10&days=30');

        $cacheKey = 'popular_articles:days:30:limit:10';
        expect(Cache::has($cacheKey))->toBeTrue();

        Article::factory()->create([
            'status' => 'published',
            'view_count' => 200,
            'published_at' => now()->subDays(3),
        ]);

        expect(Cache::has($cacheKey))->toBeFalse();
    });

    it('returns empty array when no published articles exist', function (): void {
        Article::factory()->create([
            'status' => 'draft',
            'view_count' => 100,
            'published_at' => now()->subDays(5),
        ]);

        $response = getJson('/api/articles/popular');

        $response->assertStatus(JsonResponse::HTTP_OK)
            ->assertJson(['data' => []]);
    });
});
