<?php

use App\Exceptions\TokenCreationException;

const EXPECTED_TOKEN_MESSAGE = 'Failed to create token';

describe('TokenCreationException', function (): void {
    it('creates exception with failedToCreate method', function (): void {
        $exception = TokenCreationException::failedToCreate();

        expect($exception)->toBeInstanceOf(TokenCreationException::class)
            ->and($exception)->toBeInstanceOf(RuntimeException::class)
            ->and($exception->getMessage())->toBe(EXPECTED_TOKEN_MESSAGE);
    });

    it('can be thrown and caught', function (): void {
        try {
            throw TokenCreationException::failedToCreate();
        } catch (TokenCreationException $e) {
            expect($e->getMessage())->toBe(EXPECTED_TOKEN_MESSAGE);
        }
    });

    it('has correct exception hierarchy', function (): void {
        $exception = TokenCreationException::failedToCreate();

        expect($exception)->toBeInstanceOf(RuntimeException::class)
            ->and($exception)->toBeInstanceOf(Exception::class)
            ->and($exception)->toBeInstanceOf(Throwable::class);
    });

    it('can be identified by type checking', function (): void {
        $exception = TokenCreationException::failedToCreate();

        $isTokenCreationException = $exception instanceof TokenCreationException;
        $isRuntimeException = $exception instanceof RuntimeException;

        expect($isTokenCreationException)->toBeTrue()
            ->and($isRuntimeException)->toBeTrue();
    });

    it('has zero code by default', function (): void {
        $exception = TokenCreationException::failedToCreate();

        expect($exception->getCode())->toBe(0);
    });

    it('can be used in exception handling flow', function (): void {
        $exceptionCaught = false;
        $message = '';

        try {
            throw TokenCreationException::failedToCreate();
        } catch (RuntimeException $e) {
            $exceptionCaught = true;
            $message = $e->getMessage();
        }

        expect($exceptionCaught)->toBeTrue()
            ->and($message)->toBe(EXPECTED_TOKEN_MESSAGE);
    });

    it('multiple instances have same message', function (): void {
        $exception1 = TokenCreationException::failedToCreate();
        $exception2 = TokenCreationException::failedToCreate();

        expect($exception1->getMessage())->toBe($exception2->getMessage())
            ->and($exception1->getMessage())->toBe(EXPECTED_TOKEN_MESSAGE);
    });

    it('can be caught as base exception type', function (): void {
        $caught = false;

        try {
            throw TokenCreationException::failedToCreate();
        } catch (Exception $e) {
            $caught = $e instanceof TokenCreationException;
        }

        expect($caught)->toBeTrue();
    });
});
