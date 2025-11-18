<?php

use App\Http\Middleware\CheckRevokedToken;
use App\Services\TokenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use function Pest\Laravel\mock;

use Symfony\Component\HttpFoundation\Response;

describe('CheckRevokedToken Middleware', function (): void {
    it('allows request when no token is provided', function (): void {
        $tokenService = mock(TokenService::class);

        $tokenService->shouldReceive('extractTokenId')->never();
        $tokenService->shouldReceive('isTokenRevoked')->never();

        $middleware = new CheckRevokedToken($tokenService);
        $request = Request::create('/api/test', 'GET');

        $response = $middleware->handle($request, fn ($req): Response => new Response('OK', Response::HTTP_OK));

        expect($response->getStatusCode())->toBe(Response::HTTP_OK)
            ->and($response->getContent())->toBe('OK');
    });

    it('allows request when token is valid and not revoked', function (): void {
        $tokenService = mock(TokenService::class);

        $tokenService->shouldReceive('extractTokenId')
            ->once()
            ->with('valid-token')
            ->andReturn('token-id-123');

        $tokenService->shouldReceive('isTokenRevoked')
            ->once()
            ->with('token-id-123')
            ->andReturn(false);

        $middleware = new CheckRevokedToken($tokenService);
        $request = Request::create('/api/test', 'GET');
        $request->headers->set('Authorization', 'Bearer valid-token');

        $response = $middleware->handle($request, fn ($req): Response => new Response('OK', Response::HTTP_OK));

        expect($response->getStatusCode())->toBe(Response::HTTP_OK)
            ->and($request->attributes->get('_token_id'))->toBe('token-id-123');
    });

    it('rejects request when token is revoked', function (): void {
        $tokenService = mock(TokenService::class);

        $tokenService->shouldReceive('extractTokenId')
            ->once()
            ->with('revoked-token')
            ->andReturn('revoked-token-id');

        $tokenService->shouldReceive('isTokenRevoked')
            ->once()
            ->with('revoked-token-id')
            ->andReturn(true);

        $middleware = new CheckRevokedToken($tokenService);
        $request = Request::create('/api/test', 'GET');
        $request->headers->set('Authorization', 'Bearer revoked-token');

        $response = $middleware->handle($request, fn ($req): Response => new Response('Should not reach', Response::HTTP_OK));

        expect($response->getStatusCode())->toBe(JsonResponse::HTTP_UNAUTHORIZED);

        $content = json_decode($response->getContent() ?: '{}', true);
        expect($content)->toHaveKey('message')
            ->and($content['message'])->toBe('Token has been revoked.');
    });

    it('updates token last used on terminate when token id exists', function (): void {
        $tokenService = mock(TokenService::class);

        $tokenService->shouldReceive('updateTokenLastUsed')
            ->once()
            ->with('token-id-456');

        $middleware = new CheckRevokedToken($tokenService);
        $request = Request::create('/api/test', 'GET');
        $request->attributes->set('_token_id', 'token-id-456');

        $response = new Response('OK', Response::HTTP_OK);

        $middleware->terminate($request, $response);
    });

    it('does not update token last used when token id is missing', function (): void {
        $tokenService = mock(TokenService::class);

        $tokenService->shouldReceive('updateTokenLastUsed')->never();

        $middleware = new CheckRevokedToken($tokenService);
        $request = Request::create('/api/test', 'GET');

        $response = new Response('OK', Response::HTTP_OK);

        $middleware->terminate($request, $response);
    });

    it('does not update token last used when token id is empty string', function (): void {
        $tokenService = mock(TokenService::class);

        $tokenService->shouldReceive('updateTokenLastUsed')->never();

        $middleware = new CheckRevokedToken($tokenService);
        $request = Request::create('/api/test', 'GET');
        $request->attributes->set('_token_id', '');

        $response = new Response('OK', Response::HTTP_OK);

        $middleware->terminate($request, $response);
    });

    it('does not update token last used when token id is not a string', function (): void {
        $tokenService = mock(TokenService::class);

        $tokenService->shouldReceive('updateTokenLastUsed')->never();

        $middleware = new CheckRevokedToken($tokenService);
        $request = Request::create('/api/test', 'GET');
        $request->attributes->set('_token_id', 12345);

        $response = new Response('OK', Response::HTTP_OK);

        $middleware->terminate($request, $response);
    });
});
