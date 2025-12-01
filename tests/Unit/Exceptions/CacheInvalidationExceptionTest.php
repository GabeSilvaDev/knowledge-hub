<?php

use App\Exceptions\CacheInvalidationException;
use Illuminate\Http\JsonResponse;

describe('CacheInvalidationException', function (): void {
    it('creates exception with custom message', function (): void {
        $exception = new CacheInvalidationException('test_key', 'Custom error message');

        expect($exception->getMessage())->toBe('Custom error message')
            ->and($exception->getCacheKey())->toBe('test_key');
    });

    it('creates exception with default message', function (): void {
        $exception = new CacheInvalidationException('test_key');

        expect($exception->getMessage())->toBe('Failed to invalidate cache for key: test_key')
            ->and($exception->getCacheKey())->toBe('test_key');
    });

    it('creates exception with code and previous exception', function (): void {
        $previous = new Exception('Previous error');
        $exception = new CacheInvalidationException('test_key', 'Error message', JsonResponse::HTTP_INTERNAL_SERVER_ERROR, $previous);

        expect($exception->getCode())->toBe(JsonResponse::HTTP_INTERNAL_SERVER_ERROR)
            ->and($exception->getPrevious())->toBe($previous);
    });

    it('renders exception as json response', function (): void {
        $exception = new CacheInvalidationException('test_key');
        $response = $exception->render();

        expect($response)->toBeInstanceOf(JsonResponse::class)
            ->and($response->getStatusCode())->toBe(JsonResponse::HTTP_INTERNAL_SERVER_ERROR)
            ->and($response->getData(true))->toBe([
                'message' => 'Failed to clear cache.',
                'error' => 'Cache invalidation failed',
            ]);
    });

    it('creates exception using deletionFailed factory method', function (): void {
        $exception = CacheInvalidationException::deletionFailed('popular_articles');

        expect($exception)->toBeInstanceOf(CacheInvalidationException::class)
            ->and($exception->getMessage())->toBe('Failed to delete cache with prefix: popular_articles')
            ->and($exception->getCacheKey())->toBe('popular_articles');
    });

    it('gets cache key from exception', function (): void {
        $exception = new CacheInvalidationException('my_cache_key');

        expect($exception->getCacheKey())->toBe('my_cache_key');
    });
});
