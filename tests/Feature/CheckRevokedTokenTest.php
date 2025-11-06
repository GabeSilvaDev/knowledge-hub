<?php

use App\Http\Middleware\CheckRevokedToken;
use App\Models\User;
use App\Services\TokenService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

beforeEach(function (): void {
    Redis::connection('tokens')->flushdb();
});

afterEach(function (): void {
    Redis::connection('tokens')->flushdb();
});

it('allows request when no bearer token is present', function (): void {
    $tokenService = Mockery::mock(TokenService::class);
    $middleware = new CheckRevokedToken($tokenService);

    $request = Request::create('/api/test', 'GET');
    $next = fn ($req) => response()->json(['success' => true]);

    $response = $middleware->handle($request, $next);

    expect($response->getStatusCode())->toBe(200);
});

it('allows request when token is not revoked', function (): void {
    $tokenService = Mockery::mock(TokenService::class);
    $tokenService->shouldReceive('extractTokenId')->with('valid-token')->andReturn('token-123');
    $tokenService->shouldReceive('isTokenRevoked')->with('token-123')->andReturn(false);

    $middleware = new CheckRevokedToken($tokenService);

    $request = Request::create('/api/test', 'GET');
    $request->headers->set('Authorization', 'Bearer valid-token');
    $next = fn ($req) => response()->json(['success' => true]);

    $response = $middleware->handle($request, $next);

    expect($response->getStatusCode())->toBe(200);
});

it('rejects request when token is revoked', function (): void {
    $tokenService = Mockery::mock(TokenService::class);
    $tokenService->shouldReceive('extractTokenId')->with('revoked-token')->andReturn('token-456');
    $tokenService->shouldReceive('isTokenRevoked')->with('token-456')->andReturn(true);

    $middleware = new CheckRevokedToken($tokenService);

    $request = Request::create('/api/test', 'GET');
    $request->headers->set('Authorization', 'Bearer revoked-token');
    $next = fn ($req) => response()->json(['success' => true]);

    $response = $middleware->handle($request, $next);

    expect($response->getStatusCode())->toBe(401)
        ->and($response->getData(true))->toHaveKey('message', 'Token has been revoked.');
});

it('stores token id in request attributes for terminate method', function (): void {
    $tokenService = Mockery::mock(TokenService::class);
    $tokenService->shouldReceive('extractTokenId')->with('valid-token')->andReturn('token-789');
    $tokenService->shouldReceive('isTokenRevoked')->with('token-789')->andReturn(false);

    $middleware = new CheckRevokedToken($tokenService);

    $request = Request::create('/api/test', 'GET');
    $request->headers->set('Authorization', 'Bearer valid-token');
    $next = fn ($req) => response()->json(['success' => true]);

    $middleware->handle($request, $next);

    expect($request->attributes->get('_token_id'))->toBe('token-789');
});

it('terminate method updates token last used timestamp', function (): void {
    $tokenService = Mockery::mock(TokenService::class);
    $tokenService->shouldReceive('updateTokenLastUsed')->with('token-999')->once();

    $middleware = new CheckRevokedToken($tokenService);

    $request = Request::create('/api/test', 'GET');
    $request->attributes->set('_token_id', 'token-999');
    $response = response()->json(['success' => true]);

    $middleware->terminate($request, $response);

    expect(true)->toBeTrue();
});

it('terminate method does not update when no token id in request', function (): void {
    $tokenService = Mockery::mock(TokenService::class);
    $tokenService->shouldNotReceive('updateTokenLastUsed');

    $middleware = new CheckRevokedToken($tokenService);

    $request = Request::create('/api/test', 'GET');
    $response = response()->json(['success' => true]);

    $middleware->terminate($request, $response);

    expect(true)->toBeTrue();
});

it('full flow - handle and terminate update token last used', function (): void {
    $tokenService = app(TokenService::class);

    $user = User::create([
        'name' => 'Terminate Test User',
        'email' => 'terminate' . uniqid() . '@test.com',
        'username' => 'terminateuser' . uniqid(),
        'password' => bcrypt('Password123'),
        'roles' => ['reader'],
    ]);

    $tokenResult = $tokenService->createToken($user, 'terminate_test', 3600);
    $tokenId = $tokenService->extractTokenId($tokenResult->plainTextToken);

    sleep(1);

    $initialMetadata = $tokenService->getTokenMetadata($tokenId);
    $initialLastUsed = $initialMetadata['last_used_at'] ?? null;

    $middleware = new CheckRevokedToken($tokenService);
    $request = Request::create('/api/test', 'GET');
    $request->headers->set('Authorization', 'Bearer ' . $tokenResult->plainTextToken);
    $next = fn ($req) => response()->json(['success' => true]);

    $response = $middleware->handle($request, $next);

    $middleware->terminate($request, $response);

    $updatedMetadata = $tokenService->getTokenMetadata($tokenId);
    $updatedLastUsed = $updatedMetadata['last_used_at'] ?? null;

    expect($response->getStatusCode())->toBe(200)
        ->and($updatedLastUsed)->not->toBeNull()
        ->and($updatedLastUsed)->toBeGreaterThan($initialLastUsed);

    $user->tokens()->delete();
    $user->delete();
    Redis::connection('tokens')->flushdb();
});
