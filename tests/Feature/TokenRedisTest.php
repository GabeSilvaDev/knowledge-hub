<?php

use App\Services\TokenService;
use Illuminate\Support\Facades\Redis;

beforeEach(function (): void {
    Redis::connection('tokens')->flushdb();
});

afterEach(function (): void {
    Redis::connection('tokens')->flushdb();
});

it('can store and retrieve token metadata', function (): void {
    $tokenService = app(TokenService::class);
    $tokenId = 'test_token_' . uniqid();
    $userId = 'user_123';

    $tokenService->storeTokenMetadata($tokenId, $userId, 3600);

    $metadata = $tokenService->getTokenMetadata($tokenId);

    expect($metadata)->not->toBeNull()
        ->and($metadata['user_id'])->toBe($userId)
        ->and($metadata)->toHaveKeys(['created_at', 'expires_at', 'last_used_at']);
});

it('token revocation works', function (): void {
    $tokenService = app(TokenService::class);
    $tokenId = 'test_token_' . uniqid();
    $userId = 'user_123';

    $tokenService->storeTokenMetadata($tokenId, $userId, 3600);

    expect($tokenService->isTokenRevoked($tokenId))->toBeFalse();

    $tokenService->revokeToken($tokenId);

    expect($tokenService->isTokenRevoked($tokenId))->toBeTrue();
});

it('can track multiple user tokens', function (): void {
    $tokenService = app(TokenService::class);
    $userId = 'user_456';

    $token1 = 'token_1_' . uniqid();
    $token2 = 'token_2_' . uniqid();
    $token3 = 'token_3_' . uniqid();

    $tokenService->storeTokenMetadata($token1, $userId, 3600);
    $tokenService->storeTokenMetadata($token2, $userId, 3600);
    $tokenService->storeTokenMetadata($token3, $userId, 3600);

    $count = $tokenService->countUserTokens($userId);
    expect($count)->toBe(3);

    $tokens = $tokenService->getUserTokens($userId);
    expect($tokens)->toHaveCount(3)
        ->and($tokens)->toContain($token1)
        ->and($tokens)->toContain($token2)
        ->and($tokens)->toContain($token3);
});

it('can revoke all user tokens', function (): void {
    $tokenService = app(TokenService::class);
    $userId = 'user_789';

    $token1 = 'token_1_' . uniqid();
    $token2 = 'token_2_' . uniqid();

    $tokenService->storeTokenMetadata($token1, $userId, 3600);
    $tokenService->storeTokenMetadata($token2, $userId, 3600);

    expect($tokenService->countUserTokens($userId))->toBe(2);

    $tokenService->revokeAllUserTokens($userId);

    expect($tokenService->isTokenRevoked($token1))->toBeTrue()
        ->and($tokenService->isTokenRevoked($token2))->toBeTrue()
        ->and($tokenService->countUserTokens($userId))->toBe(0);
});

it('can update last used timestamp', function (): void {
    $tokenService = app(TokenService::class);
    $tokenId = 'test_token_' . uniqid();
    $userId = 'user_111';

    $tokenService->storeTokenMetadata($tokenId, $userId, 3600);

    $initialMetadata = $tokenService->getTokenMetadata($tokenId);
    $initialLastUsed = $initialMetadata['last_used_at'];

    sleep(1);

    $tokenService->updateTokenLastUsed($tokenId);

    $updatedMetadata = $tokenService->getTokenMetadata($tokenId);
    $updatedLastUsed = $updatedMetadata['last_used_at'];

    expect($updatedLastUsed)->not->toBe($initialLastUsed);
});

it('can extract token id from sanctum token', function (): void {
    $tokenService = app(TokenService::class);
    $plainTextToken = '1|abcdefghijklmnopqrstuvwxyz1234567890';
    $tokenId = $tokenService->extractTokenId($plainTextToken);

    expect($tokenId)->not->toBeEmpty()
        ->and(strlen($tokenId))->toBe(64);
});

it('token metadata has ttl', function (): void {
    $tokenService = app(TokenService::class);
    $tokenId = 'test_token_' . uniqid();
    $userId = 'user_222';
    $ttl = 1;

    $tokenService->storeTokenMetadata($tokenId, $userId, $ttl);

    $metadata = $tokenService->getTokenMetadata($tokenId);
    expect($metadata)->not->toBeNull();

    sleep(2);

    $metadata = $tokenService->getTokenMetadata($tokenId);
    expect($metadata)->toBeNull();
});

it('redis tokens connection is separate', function (): void {
    Redis::connection('default')->set('test_key', 'default_db');
    Redis::connection('cache')->set('test_key', 'cache_db');
    Redis::connection('tokens')->set('test_key', 'tokens_db');

    expect(Redis::connection('default')->get('test_key'))->toBe('default_db')
        ->and(Redis::connection('cache')->get('test_key'))->toBe('cache_db')
        ->and(Redis::connection('tokens')->get('test_key'))->toBe('tokens_db');

    Redis::connection('default')->del('test_key');
    Redis::connection('cache')->del('test_key');
    Redis::connection('tokens')->del('test_key');
});
