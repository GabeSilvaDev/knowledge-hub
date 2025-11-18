<?php

use App\Cache\RedisCacheInvalidator;
use App\Cache\RedisCacheKeyGenerator;
use App\Exceptions\CacheInvalidationException;
use Illuminate\Support\Facades\Cache;
use Mockery\MockInterface;

describe('RedisCacheInvalidator', function (): void {
    beforeEach(function (): void {
        Cache::flush();
        test()->keyGenerator = new RedisCacheKeyGenerator;
        test()->invalidator = new RedisCacheInvalidator(test()->keyGenerator);
    });

    describe('invalidateByPrefix()', function (): void {
        it('invalidates cache by prefix successfully', function (): void {
            Cache::put('popular_articles', 'data', 60);

            expect(Cache::has('popular_articles'))->toBeTrue();

            test()->invalidator->invalidateByPrefix('popular_articles');

            expect(Cache::has('popular_articles'))->toBeFalse();
        });

        it('throws exception when invalidation fails', function (): void {
            $mockGenerator = Mockery::mock(RedisCacheKeyGenerator::class, function (MockInterface $mock): void {
                $mock->shouldReceive('invalidateByPrefix')
                    ->once()
                    ->andThrow(new Exception('Redis connection failed'));
            });

            $invalidator = new RedisCacheInvalidator($mockGenerator);

            expect(fn () => $invalidator->invalidateByPrefix('test_prefix'))
                ->toThrow(CacheInvalidationException::class);
        });
    });

    describe('invalidateArticleCache()', function (): void {
        it('invalidates popular articles cache when articleId is provided', function (): void {
            Cache::put('popular_articles', 'data', 60);

            test()->invalidator->invalidateArticleCache('123');

            expect(Cache::has('popular_articles'))->toBeFalse();
        });

        it('only invalidates popular articles when articleId is null', function (): void {
            Cache::put('popular_articles', 'data', 60);

            test()->invalidator->invalidateArticleCache(null);

            expect(Cache::has('popular_articles'))->toBeFalse();
        });
    });

    describe('invalidatePopularArticlesCache()', function (): void {
        it('invalidates popular articles cache', function (): void {
            Cache::put('popular_articles', 'data', 60);

            test()->invalidator->invalidatePopularArticlesCache();

            expect(Cache::has('popular_articles'))->toBeFalse();
        });

        it('does not affect other cache keys', function (): void {
            Cache::put('popular_articles', 'data', 60);
            Cache::put('article:123:details', 'data', 60);
            Cache::put('user:456:profile', 'data', 60);

            test()->invalidator->invalidatePopularArticlesCache();

            expect(Cache::has('popular_articles'))->toBeFalse()
                ->and(Cache::has('article:123:details'))->toBeTrue()
                ->and(Cache::has('user:456:profile'))->toBeTrue();
        });
    });
});
