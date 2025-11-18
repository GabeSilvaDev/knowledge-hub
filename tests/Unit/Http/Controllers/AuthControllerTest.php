<?php

use App\Contracts\AuthServiceInterface;
use App\Http\Controllers\AuthController;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use function Pest\Laravel\mock;

describe('AuthController Unit Tests - Edge Cases', function (): void {
    it('login returns bad request when email is not a string', function (): void {
        $authService = mock(AuthServiceInterface::class);
        $controller = new AuthController($authService);

        $request = mock(LoginRequest::class);
        $request->shouldReceive('validated')
            ->once()
            ->andReturn([
                'email' => 12345,
                'password' => 'password',
            ]);

        $response = $controller->login($request);

        expect($response)->toBeInstanceOf(JsonResponse::class)
            ->and($response->getStatusCode())->toBe(JsonResponse::HTTP_BAD_REQUEST);

        $data = $response->getData(true);
        expect($data)->toHaveKey('message')
            ->and($data['message'])->toBe('Invalid credentials format');
    });

    it('login returns bad request when password is not a string', function (): void {
        $authService = mock(AuthServiceInterface::class);
        $controller = new AuthController($authService);

        $request = mock(LoginRequest::class);
        $request->shouldReceive('validated')
            ->once()
            ->andReturn([
                'email' => 'test@example.com',
                'password' => ['not', 'a', 'string'],
            ]);

        $response = $controller->login($request);

        expect($response)->toBeInstanceOf(JsonResponse::class)
            ->and($response->getStatusCode())->toBe(JsonResponse::HTTP_BAD_REQUEST);

        $data = $response->getData(true);
        expect($data)->toHaveKey('message')
            ->and($data['message'])->toBe('Invalid credentials format');
    });

    it('logout returns unauthorized when user is not authenticated', function (): void {
        $authService = mock(AuthServiceInterface::class);
        $controller = new AuthController($authService);

        $request = mock(Request::class);
        $request->shouldReceive('user')
            ->once()
            ->andReturn(null);

        $response = $controller->logout($request);

        expect($response)->toBeInstanceOf(JsonResponse::class)
            ->and($response->getStatusCode())->toBe(JsonResponse::HTTP_UNAUTHORIZED);

        $data = $response->getData(true);
        expect($data)->toHaveKey('message')
            ->and($data['message'])->toBe('Unauthenticated');
    });

    it('revokeAll returns unauthorized when user is not authenticated', function (): void {
        $authService = mock(AuthServiceInterface::class);
        $controller = new AuthController($authService);

        $request = mock(Request::class);
        $request->shouldReceive('user')
            ->once()
            ->andReturn(null);

        $response = $controller->revokeAll($request);

        expect($response)->toBeInstanceOf(JsonResponse::class)
            ->and($response->getStatusCode())->toBe(JsonResponse::HTTP_UNAUTHORIZED);

        $data = $response->getData(true);
        expect($data)->toHaveKey('message')
            ->and($data['message'])->toBe('Unauthenticated');
    });
});
