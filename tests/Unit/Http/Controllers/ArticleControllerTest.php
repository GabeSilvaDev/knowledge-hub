<?php

use App\Http\Controllers\ArticleController;
use App\Services\ArticleService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use function Pest\Laravel\mock;

describe('ArticleController Unit Tests - Edge Cases', function (): void {
    it('popular method returns articles from service', function (): void {
        $articleService = mock(ArticleService::class);
        $controller = new ArticleController($articleService);

        $mockArticles = Collection::make([
            (object) ['id' => '1', 'title' => 'Popular 1', 'view_count' => 100],
            (object) ['id' => '2', 'title' => 'Popular 2', 'view_count' => 90],
        ]);

        $articleService->shouldReceive('getPopularArticles')
            ->once()
            ->with(10, 30)
            ->andReturn($mockArticles);

        $request = Request::create('/api/articles/popular', 'GET');

        $response = $controller->popular();

        expect($response)->toBeInstanceOf(JsonResponse::class)
            ->and($response->getStatusCode())->toBe(JsonResponse::HTTP_OK);

        $data = $response->getData(true);
        expect($data)->toHaveKey('data')
            ->and($data['data'])->toHaveCount(2);
    });

    it('popular method respects limit and days query parameters', function (): void {
        $articleService = mock(ArticleService::class);
        $controller = new ArticleController($articleService);

        $mockArticles = Collection::make([
            (object) ['id' => '1', 'title' => 'Popular 1'],
        ]);

        $articleService->shouldReceive('getPopularArticles')
            ->once()
            ->with(5, 7)
            ->andReturn($mockArticles);

        $request = Request::create('/api/articles/popular?limit=5&days=7', 'GET');
        request()->merge(['limit' => '5', 'days' => '7']);

        $response = $controller->popular();

        expect($response)->toBeInstanceOf(JsonResponse::class)
            ->and($response->getStatusCode())->toBe(JsonResponse::HTTP_OK);
    });
});
