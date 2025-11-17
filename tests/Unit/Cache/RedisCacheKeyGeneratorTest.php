<?php

use App\Cache\RedisCacheKeyGenerator;
use Illuminate\Support\Facades\Cache;

describe('RedisCacheKeyGenerator', function (): void {
    beforeEach(function (): void {
        test()->generator = new RedisCacheKeyGenerator;
        Cache::flush();
    });

    describe('generate()', function (): void {
        it('generates key with prefix only when params are empty', function (): void {
            $key = test()->generator->generate('popular_articles');

            expect($key)->toBe('popular_articles');
        });

        it('generates key with sorted parameters', function (): void {
            $key = test()->generator->generate('popular_articles', [
                'limit' => 10,
                'days' => 30,
            ]);

            expect($key)->toBe('popular_articles:days:30:limit:10');
        });

        it('sorts parameters alphabetically', function (): void {
            $key = test()->generator->generate('test', [
                'z' => 'value1',
                'a' => 'value2',
                'm' => 'value3',
            ]);

            expect($key)->toBe('test:a:value2:m:value3:z:value1');
        });

        it('handles numeric values', function (): void {
            $key = test()->generator->generate('articles', [
                'page' => 2,
                'per_page' => 15,
            ]);

            expect($key)->toBe('articles:page:2:per_page:15');
        });

        it('handles mixed parameter types', function (): void {
            $key = test()->generator->generate('mixed', [
                'string' => 'test',
                'number' => 42,
                'boolean' => true,
            ]);

            expect($key)->toContain('mixed')
                ->and($key)->toContain('string:test')
                ->and($key)->toContain('number:42')
                ->and($key)->toContain('boolean:1');
        });
    });

    describe('invalidateByPrefix()', function (): void {
        it('invalidates cache with exact prefix key', function (): void {
            Cache::put('popular_articles', 'base_data', 60);

            expect(Cache::has('popular_articles'))->toBeTrue();

            test()->generator->invalidateByPrefix('popular_articles');

            expect(Cache::has('popular_articles'))->toBeFalse();
        });

        it('handles empty prefix gracefully', function (): void {
            Cache::put('some_key', 'data', 60);

            test()->generator->invalidateByPrefix('nonexistent_prefix');

            expect(Cache::has('some_key'))->toBeTrue();
        });

        it('handles prefix with no matching keys', function (): void {
            Cache::put('article:123', 'data', 60);

            expect(function (): void {
                test()->generator->invalidateByPrefix('popular_articles');
            })->not->toThrow(Exception::class);
        });

        it('invalidates multiple keys with same prefix using registry', function (): void {
            $key1 = test()->generator->generate('popular_articles', ['limit' => 10, 'days' => 30]);
            $key2 = test()->generator->generate('popular_articles', ['limit' => 5, 'days' => 7]);
            $key3 = test()->generator->generate('popular_articles', ['limit' => 20, 'days' => 60]);

            Cache::put($key1, 'data1', 60);
            Cache::put($key2, 'data2', 60);
            Cache::put($key3, 'data3', 60);

            expect(Cache::has($key1))->toBeTrue()
                ->and(Cache::has($key2))->toBeTrue()
                ->and(Cache::has($key3))->toBeTrue();

            test()->generator->invalidateByPrefix('popular_articles');

            expect(Cache::has($key1))->toBeFalse()
                ->and(Cache::has($key2))->toBeFalse()
                ->and(Cache::has($key3))->toBeFalse();
        });

        it('does not affect keys with different prefix', function (): void {
            $key1 = test()->generator->generate('popular_articles', ['limit' => 10]);
            $key2 = test()->generator->generate('article', ['id' => 123]);

            Cache::put($key1, 'data1', 60);
            Cache::put($key2, 'data2', 60);

            test()->generator->invalidateByPrefix('popular_articles');

            expect(Cache::has($key1))->toBeFalse()
                ->and(Cache::has($key2))->toBeTrue();
        });
    });
});
