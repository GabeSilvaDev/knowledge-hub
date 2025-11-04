<?php

use App\Exceptions\ArticleRefreshException;

const EXPECTED_MESSAGE = 'Failed to refresh article after update';

describe('ArticleRefreshException', function (): void {
    it('creates exception with failedToRefresh method', function (): void {
        $exception = ArticleRefreshException::failedToRefresh();

        expect($exception)->toBeInstanceOf(ArticleRefreshException::class)
            ->and($exception)->toBeInstanceOf(RuntimeException::class)
            ->and($exception->getMessage())->toBe(EXPECTED_MESSAGE);

    });

    it('can be thrown and caught', function (): void {
        try {
            throw ArticleRefreshException::failedToRefresh();
        } catch (ArticleRefreshException $e) {
            expect($e->getMessage())->toBe(EXPECTED_MESSAGE);
        }
    });

    it('has correct exception hierarchy', function (): void {
        $exception = ArticleRefreshException::failedToRefresh();

        expect($exception)->toBeInstanceOf(RuntimeException::class)
            ->and($exception)->toBeInstanceOf(Exception::class)
            ->and($exception)->toBeInstanceOf(Throwable::class);
    });

    it('can be identified by type checking', function (): void {
        $exception = ArticleRefreshException::failedToRefresh();

        $isArticleRefreshException = $exception instanceof ArticleRefreshException;
        $isRuntimeException = $exception instanceof RuntimeException;

        expect($isArticleRefreshException)->toBeTrue()
            ->and($isRuntimeException)->toBeTrue();
    });

    it('has zero code by default', function (): void {
        $exception = ArticleRefreshException::failedToRefresh();

        expect($exception->getCode())->toBe(0);
    });

    it('can be used in exception handling flow', function (): void {
        $exceptionCaught = false;
        $message = '';

        try {
            throw ArticleRefreshException::failedToRefresh();
        } catch (RuntimeException $e) {
            $exceptionCaught = true;
            $message = $e->getMessage();
        }

        expect($exceptionCaught)->toBeTrue()
            ->and($message)->toBe(EXPECTED_MESSAGE);
    });
});
