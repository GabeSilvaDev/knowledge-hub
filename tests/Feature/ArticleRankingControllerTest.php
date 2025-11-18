<?php

use App\Contracts\ArticleRankingServiceInterface;
use App\Models\Article;
use App\Models\User;
use Illuminate\Support\Facades\Redis;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

beforeEach(function (): void {
    Redis::del('articles:ranking:views');
});

describe('GET /api/articles/ranking', function (): void {
    it('returns top ranked articles', function (): void {
        $user = User::factory()->create();
        $articles = Article::factory()->count(5)->create(['status' => 'published']);

        /** @var ArticleRankingServiceInterface $service */
        $service = app(ArticleRankingServiceInterface::class);

        foreach ($articles as $index => $article) {
            $articleId = (string) $article->id;
            $service->incrementView($articleId, ($index + 1) * 10);
        }

        $response = getJson('/api/articles/ranking?limit=3');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'rank',
                        'article_id',
                        'views',
                        'article' => [
                            'title',
                            'slug',
                            'excerpt',
                            'author_id',
                            'published_at',
                        ],
                    ],
                ],
            ])
            ->assertJsonCount(3, 'data');

        expect($response->json('data.0.views'))->toBe(50)
            ->and($response->json('data.1.views'))->toBe(40)
            ->and($response->json('data.2.views'))->toBe(30);
    });

    it('returns empty ranking when no articles viewed', function (): void {
        $response = getJson('/api/articles/ranking');

        $response->assertStatus(200)
            ->assertJson([
                'data' => [],
            ]);
    });

    it('limits ranking results correctly', function (): void {
        Article::factory()->count(15)->create(['status' => 'published']);

        /** @var ArticleRankingServiceInterface $service */
        $service = app(ArticleRankingServiceInterface::class);

        Article::all()->each(function ($article) use ($service): void {
            $service->incrementView((string) $article->id, 10);
        });

        $response = getJson('/api/articles/ranking?limit=5');

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data');
    });

    it('tracks article view on show endpoint', function (): void {
        $article = Article::factory()->create(['status' => 'published', 'view_count' => 0]);

        /** @var ArticleRankingServiceInterface $service */
        $service = app(ArticleRankingServiceInterface::class);

        $response = getJson("/api/articles/{$article->id}");

        $response->assertStatus(200);

        $score = $service->getArticleScore((string) $article->id);
        expect($score)->toBe(1.0);

        $article->refresh();
        expect($article->view_count)->toBe(1);
    });

    it('increments view count on multiple accesses', function (): void {
        $article = Article::factory()->create(['status' => 'published', 'view_count' => 0]);

        getJson("/api/articles/{$article->id}");
        getJson("/api/articles/{$article->id}");
        getJson("/api/articles/{$article->id}");

        /** @var ArticleRankingServiceInterface $service */
        $service = app(ArticleRankingServiceInterface::class);

        $score = $service->getArticleScore((string) $article->id);
        expect($score)->toBe(3.0);

        $article->refresh();
        expect($article->view_count)->toBe(3);
    });
});

describe('GET /api/articles/ranking/statistics', function (): void {
    it('returns ranking statistics', function (): void {
        $articles = Article::factory()->count(3)->create(['status' => 'published']);

        /** @var ArticleRankingServiceInterface $service */
        $service = app(ArticleRankingServiceInterface::class);

        $service->incrementView((string) $articles[0]->id, 100);
        $service->incrementView((string) $articles[1]->id, 50);
        $service->incrementView((string) $articles[2]->id, 25);

        $response = getJson('/api/articles/ranking/statistics');

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'total_articles' => 3,
                    'total_views' => 175.0,
                    'top_score' => 100.0,
                ],
            ]);
    });
});

describe('POST /api/articles/ranking/sync', function (): void {
    it('syncs ranking from database', function (): void {
        Article::query()->forceDelete();

        $user = User::factory()->create();
        Sanctum::actingAs($user);

        Article::factory()->create([
            'status' => 'published',
            'view_count' => 100,
        ]);

        Article::factory()->create([
            'status' => 'published',
            'view_count' => 50,
        ]);

        $response = postJson('/api/articles/ranking/sync');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Ranking sincronizado com sucesso.',
            ]);

        /** @var ArticleRankingServiceInterface $service */
        $service = app(ArticleRankingServiceInterface::class);
        $stats = $service->getStatistics();

        expect($stats['total_articles'])->toBe(2)
            ->and($stats['total_views'])->toBe(150.0);
    });
});

describe('GET /api/articles/{id}/ranking', function (): void {
    it('shows article ranking info', function (): void {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $article = Article::factory()->create(['status' => 'published']);

        /** @var ArticleRankingServiceInterface $service */
        $service = app(ArticleRankingServiceInterface::class);
        $service->incrementView((string) $article->id, 42);

        $response = getJson("/api/articles/{$article->id}/ranking");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'article_id' => (string) $article->id,
                    'rank' => 1,
                    'views' => 42,
                ],
            ]);
    });
});
