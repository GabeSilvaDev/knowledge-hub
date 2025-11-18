<?php

use App\Contracts\ArticleRankingServiceInterface;
use App\Http\Middleware\TrackArticleView;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\HttpFoundation\Response;

beforeEach(function (): void {
    Redis::del('articles:ranking:views');
});

it('increments view count when called with article in route', function (): void {
    $article = Article::factory()->create(['view_count' => 5]);

    /** @var ArticleRankingServiceInterface $service */
    $service = app(ArticleRankingServiceInterface::class);

    $middleware = new TrackArticleView($service);

    $request = Request::create('/api/articles/' . $article->id, 'GET');

    $request->setRouteResolver(fn (): object => new readonly class($article)
    {
        public function __construct(private Article $article) {}

        public function parameter(string $name, mixed $default = null): mixed
        {
            return $name === 'article' ? $this->article : $default;
        }
    });

    $response = $middleware->handle($request, fn ($req): Response => new Response('OK', Response::HTTP_OK));

    expect($response->getStatusCode())->toBe(Response::HTTP_OK);

    $score = $service->getArticleScore((string) $article->id);
    expect($score)->toBe(1.0);

    $article->refresh();
    expect($article->view_count)->toBe(6);
});

it('does not track view on non-GET request', function (): void {
    $article = Article::factory()->create(['view_count' => 5]);

    /** @var ArticleRankingServiceInterface $service */
    $service = app(ArticleRankingServiceInterface::class);

    $middleware = new TrackArticleView($service);

    $request = Request::create('/api/articles/' . $article->id, 'POST');

    $request->setRouteResolver(fn (): object => new readonly class($article)
    {
        public function __construct(private Article $article) {}

        public function parameter(string $name, mixed $default = null): mixed
        {
            return $name === 'article' ? $this->article : $default;
        }
    });

    $middleware->handle($request, fn ($req): Response => new Response('OK', Response::HTTP_OK));

    $score = $service->getArticleScore((string) $article->id);
    expect($score)->toBe(0.0);

    $article->refresh();
    expect($article->view_count)->toBe(5);
});

it('does not track view when no article in route', function (): void {
    /** @var ArticleRankingServiceInterface $service */
    $service = app(ArticleRankingServiceInterface::class);

    $middleware = new TrackArticleView($service);

    $request = Request::create('/api/articles', 'GET');

    $request->setRouteResolver(fn (): object => new class
    {
        public function parameter(string $name, mixed $default = null): mixed
        {
            return $default;
        }
    });

    $middleware->handle($request, fn ($req): Response => new Response('OK', Response::HTTP_OK));

    $stats = $service->getStatistics();
    expect($stats['total_articles'])->toBe(0);
});

it('does not create article version when incrementing view count', function (): void {
    $article = Article::factory()->create(['view_count' => 0]);

    /** @var ArticleRankingServiceInterface $service */
    $service = app(ArticleRankingServiceInterface::class);

    $middleware = new TrackArticleView($service);

    $request = Request::create('/api/articles/' . $article->id, 'GET');

    $request->setRouteResolver(fn (): object => new readonly class($article)
    {
        public function __construct(private Article $article) {}

        public function parameter(string $name, mixed $default = null): mixed
        {
            return $name === 'article' ? $this->article : $default;
        }
    });

    $middleware->handle($request, fn ($req): Response => new Response('OK', Response::HTTP_OK));

    expect($article->versions)->toHaveCount(0);
});
