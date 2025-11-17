<?php

use App\Cache\RedisCacheKeyGenerator;
use App\Models\Article;
use App\Models\User;
use App\Repositories\ArticleRepository;
use App\Services\ArticleService;
use Illuminate\Support\Facades\Cache;

describe('ArticleService::getPopularArticles()', function (): void {
    beforeEach(function (): void {
        Cache::flush();
        Article::truncate();
        User::truncate();

        test()->repository = app(ArticleRepository::class);
        test()->keyGenerator = app(RedisCacheKeyGenerator::class);
        test()->service = new ArticleService(test()->repository, test()->keyGenerator);
    });

    it('returns popular articles from repository', function (): void {
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

        $articles = test()->service->getPopularArticles(10, 30);

        expect($articles)->toHaveCount(2)
            ->and($articles->first()->view_count)->toBe(200)
            ->and($articles->last()->view_count)->toBe(100);
    });

    it('caches popular articles', function (): void {
        Article::factory()->create([
            'status' => 'published',
            'view_count' => 100,
            'published_at' => now()->subDays(5),
        ]);

        test()->service->getPopularArticles(10, 30);

        $cacheKey = 'popular_articles:days:30:limit:10';
        expect(Cache::has($cacheKey))->toBeTrue();
    });

    it('returns cached data on subsequent calls', function (): void {
        Article::factory()->create([
            'status' => 'published',
            'view_count' => 100,
            'published_at' => now()->subDays(5),
        ]);

        $articles1 = test()->service->getPopularArticles(10, 30);

        $articles2 = test()->service->getPopularArticles(10, 30);

        expect($articles1->count())->toBe($articles2->count())
            ->and($articles1->count())->toBe(1);
    });

    it('respects limit parameter', function (): void {
        Article::factory()->count(20)->create([
            'status' => 'published',
            'published_at' => now()->subDays(5),
        ]);

        $articles = test()->service->getPopularArticles(5, 30);

        expect($articles)->toHaveCount(5);
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

        $articles = test()->service->getPopularArticles(10, 30);

        expect($articles)->toHaveCount(1)
            ->and($articles->first()->view_count)->toBe(100);
    });

    it('uses default parameters when not specified', function (): void {
        Article::factory()->create([
            'status' => 'published',
            'view_count' => 100,
            'published_at' => now()->subDays(5),
        ]);

        $articles = test()->service->getPopularArticles();

        expect($articles)->toHaveCount(1);

        $cacheKey = 'popular_articles:days:30:limit:10';
        expect(Cache::has($cacheKey))->toBeTrue();
    });

    it('generates different cache keys for different parameters', function (): void {
        Article::factory()->count(15)->create([
            'status' => 'published',
            'published_at' => now()->subDays(5),
        ]);

        test()->service->getPopularArticles(10, 30);
        test()->service->getPopularArticles(5, 7);
        test()->service->getPopularArticles(20, 90);

        expect(Cache::has('popular_articles:days:30:limit:10'))->toBeTrue()
            ->and(Cache::has('popular_articles:days:7:limit:5'))->toBeTrue()
            ->and(Cache::has('popular_articles:days:90:limit:20'))->toBeTrue();
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

        $articles = test()->service->getPopularArticles();

        expect($articles)->toHaveCount(1)
            ->and($articles->first()->status)->toBe('published');
    });
});
