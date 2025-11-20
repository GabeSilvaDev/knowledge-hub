<?php

use App\Contracts\ArticleRepositoryInterface;
use App\Models\Article;
use App\Services\ArticleRankingService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Redis;

beforeEach(function (): void {
    Redis::del('articles:ranking:views');

    $this->repository = Mockery::mock(ArticleRepositoryInterface::class);
    $this->service = new ArticleRankingService($this->repository);
});

it('increments view count for article', function (): void {
    $this->service->incrementView('article-123', 5);

    $score = Redis::zscore('articles:ranking:views', 'article-123');
    expect($score)->toBe(5.0);
});

it('increments view count multiple times', function (): void {
    $this->service->incrementView('article-456');
    $this->service->incrementView('article-456', 3);
    $this->service->incrementView('article-456', 2);

    $score = Redis::zscore('articles:ranking:views', 'article-456');
    expect($score)->toBe(6.0);
});

it('returns top articles in descending order', function (): void {
    $this->service->incrementView('article-1', 100);
    $this->service->incrementView('article-2', 200);
    $this->service->incrementView('article-3', 150);

    $top = $this->service->getTopArticles(3);

    expect($top)->toHaveCount(3)
        ->and($top[0]['article_id'])->toBe('article-2')
        ->and($top[0]['score'])->toBe(200.0)
        ->and($top[1]['article_id'])->toBe('article-3')
        ->and($top[1]['score'])->toBe(150.0)
        ->and($top[2]['article_id'])->toBe('article-1')
        ->and($top[2]['score'])->toBe(100.0);
});

it('limits top articles results', function (): void {
    for ($i = 1; $i <= 20; $i++) {
        $this->service->incrementView("article-{$i}", $i * 10);
    }

    $top = $this->service->getTopArticles(5);

    expect($top)->toHaveCount(5)
        ->and($top[0]['score'])->toBe(200.0)
        ->and($top[4]['score'])->toBe(160.0);
});

it('returns article rank position', function (): void {
    $this->service->incrementView('article-1', 100);
    $this->service->incrementView('article-2', 200);
    $this->service->incrementView('article-3', 150);

    $rank1 = $this->service->getArticleRank('article-2');
    $rank2 = $this->service->getArticleRank('article-3');
    $rank3 = $this->service->getArticleRank('article-1');

    expect($rank1)->toBe(1)
        ->and($rank2)->toBe(2)
        ->and($rank3)->toBe(3);
});

it('returns null for non-existing article rank', function (): void {
    $rank = $this->service->getArticleRank('non-existing');

    expect($rank)->toBeNull();
});

it('returns article score', function (): void {
    $this->service->incrementView('article-test', 42);

    $score = $this->service->getArticleScore('article-test');

    expect($score)->toBe(42.0);
});

it('returns zero score for non-existing article', function (): void {
    $score = $this->service->getArticleScore('non-existing');

    expect($score)->toBe(0.0);
});

it('removes article from ranking', function (): void {
    $this->service->incrementView('article-remove', 50);

    expect($this->service->getArticleScore('article-remove'))->toBe(50.0);

    $this->service->removeArticle('article-remove');

    expect($this->service->getArticleScore('article-remove'))->toBe(0.0);
});

it('resets entire ranking', function (): void {
    $this->service->incrementView('article-1', 100);
    $this->service->incrementView('article-2', 200);
    $this->service->incrementView('article-3', 150);

    $this->service->resetRanking();

    $top = $this->service->getTopArticles(10);
    expect($top)->toHaveCount(0);
});

it('calculates statistics correctly', function (): void {
    $this->service->incrementView('article-1', 100);
    $this->service->incrementView('article-2', 50);
    $this->service->incrementView('article-3', 25);

    $stats = $this->service->getStatistics();

    expect($stats)->toBeArray()
        ->and($stats['total_articles'])->toBe(3)
        ->and($stats['total_views'])->toBe(175.0)
        ->and($stats['top_score'])->toBe(100.0);
});

it('returns zero statistics for empty ranking', function (): void {
    $stats = $this->service->getStatistics();

    expect($stats)->toBeArray()
        ->and($stats['total_articles'])->toBe(0)
        ->and($stats['total_views'])->toBe(0.0)
        ->and($stats['top_score'])->toBe(0.0);
});

it('syncs from database correctly', function (): void {
    Article::query()->forceDelete();

    $article1 = Article::factory()->create([
        'status' => 'published',
        'view_count' => 100,
    ]);

    $article2 = Article::factory()->create([
        'status' => 'published',
        'view_count' => 50,
    ]);

    $article3Draft = Article::factory()->create([
        'status' => 'draft',
        'view_count' => 200,
    ]);

    $article4NoViews = Article::factory()->create([
        'status' => 'published',
        'view_count' => 0,
    ]);

    // Mock repository para retornar apenas published com views > 0
    $this->repository
        ->shouldReceive('getPublishedWithViews')
        ->once()
        ->andReturn(new Collection([$article1, $article2]));

    $this->service->syncFromDatabase();

    $top = $this->service->getTopArticles(10);

    $articleIds = $top->pluck('article_id')->toArray();

    expect($top)->toHaveCount(2)
        ->and($articleIds)->toContain((string) $article1->id)
        ->and($articleIds)->toContain((string) $article2->id);

    $article1Score = $top->firstWhere('article_id', (string) $article1->id);
    $article2Score = $top->firstWhere('article_id', (string) $article2->id);

    expect($article1Score['score'])->toBe(100.0)
        ->and($article2Score['score'])->toBe(50.0);
});

it('handles ranking key expiration', function (): void {
    $this->service->incrementView('article-1', 10);

    $ttl = Redis::ttl('articles:ranking:views');

    expect($ttl)->toBeGreaterThan(7775000)
        ->and($ttl)->toBeLessThanOrEqual(7776000);
});
