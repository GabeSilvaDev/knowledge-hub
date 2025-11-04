<?php

use App\Exceptions\ResourceNotFoundException;
use Illuminate\Http\JsonResponse;

const ARTICLE_DEFAULT_MESSAGE = 'O recurso solicitado (Article) não foi encontrado.';
const USER_DEFAULT_MESSAGE = 'O recurso solicitado (User) não foi encontrado.';
const ERROR_TYPE = 'Resource not found';

describe('ResourceNotFoundException', function (): void {
    it('creates exception with default message when no message provided', function (): void {
        $exception = new ResourceNotFoundException('Article');

        expect($exception)->toBeInstanceOf(ResourceNotFoundException::class)
            ->and($exception)->toBeInstanceOf(Exception::class)
            ->and($exception->getMessage())->toBe(ARTICLE_DEFAULT_MESSAGE)
            ->and($exception->getResourceName())->toBe('Article');
    });

    it('creates exception with custom message when provided', function (): void {
        $exception = new ResourceNotFoundException('User', 'Custom error message');

        expect($exception->getMessage())->toBe('Custom error message')
            ->and($exception->getResourceName())->toBe('User');
    });

    it('creates exception with custom code', function (): void {
        $exception = new ResourceNotFoundException('Article', '', 404);

        expect($exception->getCode())->toBe(404)
            ->and($exception->getResourceName())->toBe('Article');
    });

    it('creates exception with previous exception', function (): void {
        $previous = new Exception('Previous exception');
        $exception = new ResourceNotFoundException('Article', '', 0, $previous);

        expect($exception->getPrevious())->toBe($previous)
            ->and($exception->getPrevious()->getMessage())->toBe('Previous exception');
    });

    it('renders json response with 404 status code', function (): void {
        $exception = new ResourceNotFoundException('Article');
        $response = $exception->render();

        expect($response)->toBeInstanceOf(JsonResponse::class)
            ->and($response->getStatusCode())->toBe(404)
            ->and($response->getData(true))->toBe([
                'message' => ARTICLE_DEFAULT_MESSAGE,
                'error' => ERROR_TYPE,
            ]);
    });

    it('renders json response with custom message', function (): void {
        $exception = new ResourceNotFoundException('User', 'User not found in database');
        $response = $exception->render();

        expect($response->getData(true))->toBe([
            'message' => 'User not found in database',
            'error' => ERROR_TYPE,
        ])
            ->and($response->getStatusCode())->toBe(404);
    });

    it('can be thrown and caught', function (): void {
        try {
            throw new ResourceNotFoundException('Article');
        } catch (ResourceNotFoundException $e) {
            expect($e->getMessage())->toBe(ARTICLE_DEFAULT_MESSAGE)
                ->and($e->getResourceName())->toBe('Article');
        }
    });

    it('has correct exception hierarchy', function (): void {
        $exception = new ResourceNotFoundException('Article');

        expect($exception)->toBeInstanceOf(Exception::class)
            ->and($exception)->toBeInstanceOf(Throwable::class);
    });

    it('can be identified by type checking', function (): void {
        $exception = new ResourceNotFoundException('Article');

        $isResourceNotFoundException = $exception instanceof ResourceNotFoundException;
        $isException = $exception instanceof Exception;

        expect($isResourceNotFoundException)->toBeTrue()
            ->and($isException)->toBeTrue();
    });

    it('preserves resource name with different resource types', function (): void {
        $articleException = new ResourceNotFoundException('Article');
        $userException = new ResourceNotFoundException('User');
        $commentException = new ResourceNotFoundException('Comment');

        expect($articleException->getResourceName())->toBe('Article')
            ->and($userException->getResourceName())->toBe('User')
            ->and($commentException->getResourceName())->toBe('Comment');
    });

    it('has zero code by default', function (): void {
        $exception = new ResourceNotFoundException('Article');

        expect($exception->getCode())->toBe(0);
    });

    it('can be used in exception handling flow', function (): void {
        $exceptionCaught = false;
        $resourceName = '';

        try {
            throw new ResourceNotFoundException('Article');
        } catch (Exception $e) {
            $exceptionCaught = true;
            if ($e instanceof ResourceNotFoundException) {
                $resourceName = $e->getResourceName();
            }
        }

        expect($exceptionCaught)->toBeTrue()
            ->and($resourceName)->toBe('Article');
    });

    it('renders consistent response structure', function (): void {
        $exception = new ResourceNotFoundException('Article');
        $response = $exception->render();
        $data = $response->getData(true);

        expect($data)->toHaveKey('message')
            ->and($data)->toHaveKey('error')
            ->and($data['error'])->toBe(ERROR_TYPE);
    });

    it('creates different messages for different resources with default message', function (): void {
        $articleException = new ResourceNotFoundException('Article');
        $userException = new ResourceNotFoundException('User');

        expect($articleException->getMessage())->toBe(ARTICLE_DEFAULT_MESSAGE)
            ->and($userException->getMessage())->toBe(USER_DEFAULT_MESSAGE);
    });
});
