<?php

use App\Contracts\CacheInvalidatorInterface;
use App\Models\Article;
use App\Observers\ArticleObserver;
use Illuminate\Support\Facades\Cache;

describe('ArticleObserver', function (): void {
    beforeEach(function (): void {
        Cache::flush();

        test()->cacheInvalidator = Mockery::mock(CacheInvalidatorInterface::class);
        test()->observer = new ArticleObserver(test()->cacheInvalidator);
    });

    it('invalidates cache when article is created', function (): void {
        $article = Article::factory()->make(['id' => '123']);

        test()->cacheInvalidator
            ->shouldReceive('invalidateArticleCache')
            ->once()
            ->with('123');

        test()->observer->created($article);
    });

    it('invalidates cache when article is updated', function (): void {
        $article = Article::factory()->make(['id' => '456']);

        test()->cacheInvalidator
            ->shouldReceive('invalidateArticleCache')
            ->once()
            ->with('456');

        test()->observer->updated($article);
    });

    it('invalidates cache when article is deleted', function (): void {
        $article = Article::factory()->make(['id' => '789']);

        test()->cacheInvalidator
            ->shouldReceive('invalidateArticleCache')
            ->once()
            ->with('789');

        test()->observer->deleted($article);
    });

    it('invalidates cache when article is restored', function (): void {
        $article = Article::factory()->make(['id' => 'abc']);

        test()->cacheInvalidator
            ->shouldReceive('invalidateArticleCache')
            ->once()
            ->with('abc');

        test()->observer->restored($article);
    });

    it('handles null article id gracefully', function (): void {
        $article = Article::factory()->make(['id' => null]);

        test()->cacheInvalidator
            ->shouldReceive('invalidateArticleCache')
            ->once()
            ->with(null);

        test()->observer->created($article);
    });
});
