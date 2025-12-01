<?php

use App\Contracts\SearchServiceInterface;
use App\Models\Article;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

beforeEach(function (): void {
    Article::query()->delete();
    User::query()->delete();

    $this->user = User::factory()->create();
    actingAs($this->user);
});

describe('SearchController Feature Tests', function (): void {
    it('searches articles with query and filters', function (): void {
        $articles = Article::factory()->count(3)->create(['status' => 'published']);

        $mockPaginator = new LengthAwarePaginator(
            items: $articles,
            total: 3,
            perPage: 15,
            currentPage: 1
        );

        $this->mock(SearchServiceInterface::class)
            ->shouldReceive('search')
            ->once()
            ->andReturn($mockPaginator);

        $response = getJson('/api/search?q=laravel&per_page=15');

        $response->assertStatus(JsonResponse::HTTP_OK)
            ->assertJsonStructure([
                'data',
                'meta' => [
                    'total',
                    'per_page',
                    'current_page',
                    'last_page',
                    'from',
                    'to',
                ],
                'links' => [
                    'first',
                    'last',
                    'prev',
                    'next',
                ],
            ]);
    });

    it('returns autocomplete suggestions', function (): void {
        Article::factory()->create([
            'title' => 'Laravel Tutorial',
            'status' => 'published',
        ]);

        $response = getJson('/api/search/autocomplete?q=lar');

        $response->assertStatus(JsonResponse::HTTP_OK)
            ->assertJsonStructure([
                'data',
            ]);
    });

    it('syncs articles to search index', function (): void {
        Article::factory()->count(5)->create(['status' => 'published']);

        $response = postJson('/api/search/sync');

        $response->assertStatus(JsonResponse::HTTP_OK)
            ->assertJsonStructure([
                'message',
                'count',
            ]);
    });
});
