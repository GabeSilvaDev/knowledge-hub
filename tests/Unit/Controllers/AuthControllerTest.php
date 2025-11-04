<?php

use App\Contracts\AuthServiceInterface;
use App\Http\Controllers\AuthController;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use function Pest\Laravel\mock;

describe('AuthController Unit Tests', function (): void {
    describe('login method edge cases', function (): void {
        it('returns bad request when email is not a string', function (): void {
            /** @var AuthServiceInterface&\Mockery\MockInterface $authService */
            $authService = mock(AuthServiceInterface::class);
            $controller = new AuthController($authService);

            /** @var LoginRequest&\Mockery\MockInterface $request */
            $request = mock(LoginRequest::class);
            $request->shouldReceive('validated')
                ->once()
                ->andReturn([
                    'email' => 123,
                    'password' => 'password',
                ]);

            $response = $controller->login($request);

            expect($response)->toBeInstanceOf(JsonResponse::class)
                ->and($response->getStatusCode())->toBe(JsonResponse::HTTP_BAD_REQUEST)
                ->and($response->getData(true))->toBe([
                    'message' => 'Invalid credentials format',
                ]);
        });

        it('returns bad request when password is not a string', function (): void {
            /** @var AuthServiceInterface&\Mockery\MockInterface $authService */
            $authService = mock(AuthServiceInterface::class);
            $controller = new AuthController($authService);

            /** @var LoginRequest&\Mockery\MockInterface $request */
            $request = mock(LoginRequest::class);
            $request->shouldReceive('validated')
                ->once()
                ->andReturn([
                    'email' => 'test@example.com',
                    'password' => ['not-a-string'],
                ]);

            $response = $controller->login($request);

            expect($response)->toBeInstanceOf(JsonResponse::class)
                ->and($response->getStatusCode())->toBe(JsonResponse::HTTP_BAD_REQUEST)
                ->and($response->getData(true))->toBe([
                    'message' => 'Invalid credentials format',
                ]);
        });

        it('returns bad request when both are not strings', function (): void {
            /** @var AuthServiceInterface&\Mockery\MockInterface $authService */
            $authService = mock(AuthServiceInterface::class);
            $controller = new AuthController($authService);

            /** @var LoginRequest&\Mockery\MockInterface $request */
            $request = mock(LoginRequest::class);
            $request->shouldReceive('validated')
                ->once()
                ->andReturn([
                    'email' => null,
                    'password' => null,
                ]);

            $response = $controller->login($request);

            expect($response)->toBeInstanceOf(JsonResponse::class)
                ->and($response->getStatusCode())->toBe(JsonResponse::HTTP_BAD_REQUEST);
        });
    });

    describe('logout method edge cases', function (): void {
        it('returns unauthorized when user is null', function (): void {
            /** @var AuthServiceInterface&\Mockery\MockInterface $authService */
            $authService = mock(AuthServiceInterface::class);
            $controller = new AuthController($authService);

            /** @var Request&\Mockery\MockInterface $request */
            $request = mock(Request::class);
            $request->shouldReceive('user')
                ->once()
                ->andReturn(null);

            $response = $controller->logout($request);

            expect($response)->toBeInstanceOf(JsonResponse::class)
                ->and($response->getStatusCode())->toBe(JsonResponse::HTTP_UNAUTHORIZED)
                ->and($response->getData(true))->toBe([
                    'message' => 'Unauthenticated',
                ]);
        });

        it('returns unauthorized when user is wrong type', function (): void {
            /** @var AuthServiceInterface&\Mockery\MockInterface $authService */
            $authService = mock(AuthServiceInterface::class);
            $controller = new AuthController($authService);

            /** @var Request&\Mockery\MockInterface $request */
            $request = mock(Request::class);
            $request->shouldReceive('user')
                ->once()
                ->andReturn(new stdClass);

            $response = $controller->logout($request);

            expect($response)->toBeInstanceOf(JsonResponse::class)
                ->and($response->getStatusCode())->toBe(JsonResponse::HTTP_UNAUTHORIZED);
        });
    });

    describe('revokeAll method edge cases', function (): void {
        it('returns unauthorized when user is null', function (): void {
            /** @var AuthServiceInterface&\Mockery\MockInterface $authService */
            $authService = mock(AuthServiceInterface::class);
            $controller = new AuthController($authService);

            /** @var Request&\Mockery\MockInterface $request */
            $request = mock(Request::class);
            $request->shouldReceive('user')
                ->once()
                ->andReturn(null);

            $response = $controller->revokeAll($request);

            expect($response)->toBeInstanceOf(JsonResponse::class)
                ->and($response->getStatusCode())->toBe(JsonResponse::HTTP_UNAUTHORIZED)
                ->and($response->getData(true))->toBe([
                    'message' => 'Unauthenticated',
                ]);
        });

        it('returns unauthorized when user is wrong type', function (): void {
            /** @var AuthServiceInterface&\Mockery\MockInterface $authService */
            $authService = mock(AuthServiceInterface::class);
            $controller = new AuthController($authService);

            /** @var Request&\Mockery\MockInterface $request */
            $request = mock(Request::class);
            $request->shouldReceive('user')
                ->once()
                ->andReturn(new stdClass);

            $response = $controller->revokeAll($request);

            expect($response)->toBeInstanceOf(JsonResponse::class)
                ->and($response->getStatusCode())->toBe(JsonResponse::HTTP_UNAUTHORIZED);
        });
    });
});
