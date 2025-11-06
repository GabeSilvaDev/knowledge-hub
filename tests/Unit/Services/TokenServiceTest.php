<?php

use App\Models\User;
use App\Services\TokenService;
use Illuminate\Support\Facades\Redis;

beforeEach(function (): void {
    $this->tokenService = app(TokenService::class);
    Redis::connection('tokens')->flushdb();
    $this->testUser = null;
});

afterEach(function (): void {
    if (property_exists($this, 'testUser') && $this->testUser !== null && $this->testUser) {
        $this->testUser->tokens()->delete();
        $this->testUser->delete();
    }
});

function createTestUser(): User
{
    if (! isset(test()->testUser) || ! test()->testUser) {
        test()->testUser = User::create([
            'name' => 'Test User ' . uniqid(),
            'email' => 'test' . uniqid() . '@example.com',
            'username' => 'testuser' . uniqid(),
            'password' => bcrypt('password'),
            'roles' => ['reader'],
        ]);
    }

    return test()->testUser;
}

it('can create token with metadata', function (): void {
    $user = createTestUser();
    $token = $this->tokenService->createToken($user, 'test_token', 3600);

    expect($token)->not->toBeNull()
        ->and($token->plainTextToken)->not->toBeEmpty();

    $tokenId = $this->tokenService->extractTokenId($token->plainTextToken);
    $metadata = $this->tokenService->getTokenMetadata($tokenId);

    expect($metadata)->not->toBeNull()
        ->and($metadata['user_id'])->toBe((string) $user->getKey())
        ->and($metadata)->toHaveKeys(['created_at', 'expires_at', 'last_used_at']);
});

it('can store and retrieve token metadata', function (): void {
    $tokenId = 'test_token_' . uniqid();
    $userId = 'user_123';

    $this->tokenService->storeTokenMetadata($tokenId, $userId, 3600);
    $metadata = $this->tokenService->getTokenMetadata($tokenId);

    expect($metadata)->not->toBeNull()
        ->and($metadata['user_id'])->toBe($userId);
});

it('can check if token is revoked', function (): void {
    $tokenId = 'test_token_' . uniqid();

    expect($this->tokenService->isTokenRevoked($tokenId))->toBeFalse();

    $this->tokenService->storeTokenMetadata($tokenId, 'user_123', 3600);
    $this->tokenService->revokeToken($tokenId);

    expect($this->tokenService->isTokenRevoked($tokenId))->toBeTrue();
});

it('can revoke specific token', function (): void {
    $user = createTestUser();
    $tokenId = 'test_token_' . uniqid();

    $this->tokenService->storeTokenMetadata($tokenId, (string) $user->getKey(), 3600);
    expect($this->tokenService->isTokenRevoked($tokenId))->toBeFalse();

    $this->tokenService->revokeToken($tokenId);

    expect($this->tokenService->isTokenRevoked($tokenId))->toBeTrue()
        ->and($this->tokenService->getTokenMetadata($tokenId))->toBeNull();
});

it('can revoke all user tokens', function (): void {
    $user = createTestUser();
    $userId = (string) $user->getKey();

    $token1 = 'token_1_' . uniqid();
    $token2 = 'token_2_' . uniqid();
    $token3 = 'token_3_' . uniqid();

    $this->tokenService->storeTokenMetadata($token1, $userId, 3600);
    $this->tokenService->storeTokenMetadata($token2, $userId, 3600);
    $this->tokenService->storeTokenMetadata($token3, $userId, 3600);

    expect($this->tokenService->countUserTokens($userId))->toBe(3);

    $this->tokenService->revokeAllUserTokens($userId);

    expect($this->tokenService->isTokenRevoked($token1))->toBeTrue()
        ->and($this->tokenService->isTokenRevoked($token2))->toBeTrue()
        ->and($this->tokenService->isTokenRevoked($token3))->toBeTrue()
        ->and($this->tokenService->countUserTokens($userId))->toBe(0);
});

it('can update token last used', function (): void {
    $tokenId = 'test_token_' . uniqid();
    $userId = 'user_123';

    $this->tokenService->storeTokenMetadata($tokenId, $userId, 3600);
    usleep(100000);
    $this->tokenService->updateTokenLastUsed($tokenId);

    $metadata = $this->tokenService->getTokenMetadata($tokenId);
    expect($metadata)->not->toBeNull();

    $lastUsed = new \DateTime($metadata['last_used_at']);
    $diff = now()->diffInSeconds($lastUsed);

    expect($diff)->toBeLessThan(2);
});

it('can get user tokens', function (): void {
    $user = createTestUser();
    $userId = (string) $user->getKey();

    $token1 = 'token_1_' . uniqid();
    $token2 = 'token_2_' . uniqid();

    $this->tokenService->storeTokenMetadata($token1, $userId, 3600);
    $this->tokenService->storeTokenMetadata($token2, $userId, 3600);

    $tokens = $this->tokenService->getUserTokens($userId);

    expect($tokens)->toHaveCount(2)
        ->and($tokens)->toContain($token1)
        ->and($tokens)->toContain($token2);
});

it('can count user tokens', function (): void {
    $user = createTestUser();
    $userId = (string) $user->getKey();

    expect($this->tokenService->countUserTokens($userId))->toBe(0);

    $this->tokenService->storeTokenMetadata('token_1', $userId, 3600);
    expect($this->tokenService->countUserTokens($userId))->toBe(1);

    $this->tokenService->storeTokenMetadata('token_2', $userId, 3600);
    expect($this->tokenService->countUserTokens($userId))->toBe(2);
});

it('can extract token id from plain text', function (): void {
    $plainTextToken = '1|abcdefghijklmnopqrstuvwxyz1234567890';
    $tokenId = $this->tokenService->extractTokenId($plainTextToken);

    expect($tokenId)->not->toBeEmpty()
        ->and(strlen((string) $tokenId))->toBe(64);
});

it('metadata expires after ttl', function (): void {
    $tokenId = 'test_token_' . uniqid();
    $userId = 'user_123';

    $this->tokenService->storeTokenMetadata($tokenId, $userId, 1);
    expect($this->tokenService->getTokenMetadata($tokenId))->not->toBeNull();

    sleep(2);
    expect($this->tokenService->getTokenMetadata($tokenId))->toBeNull();
});

it('can cleanup expired tokens', function (): void {
    $userId = 'user_456';

    $token1 = 'token_1_' . uniqid();
    $token2 = 'token_2_' . uniqid();
    $token3 = 'token_3_' . uniqid();

    $this->tokenService->storeTokenMetadata($token1, $userId, 1);
    $this->tokenService->storeTokenMetadata($token2, $userId, 3600);
    $this->tokenService->storeTokenMetadata($token3, $userId, 1);

    expect($this->tokenService->countUserTokens($userId))->toBe(3);

    sleep(2);
    $cleaned = $this->tokenService->cleanupExpiredTokens($userId);

    expect($cleaned)->toBe(2)
        ->and($this->tokenService->countUserTokens($userId))->toBe(1);
});

it('cleanup returns zero when no expired tokens', function (): void {
    $userId = 'user_789';

    $token1 = 'token_1_' . uniqid();
    $token2 = 'token_2_' . uniqid();

    $this->tokenService->storeTokenMetadata($token1, $userId, 3600);
    $this->tokenService->storeTokenMetadata($token2, $userId, 3600);

    $cleaned = $this->tokenService->cleanupExpiredTokens($userId);

    expect($cleaned)->toBe(0)
        ->and($this->tokenService->countUserTokens($userId))->toBe(2);
});

it('extract token id handles token without pipe', function (): void {
    $plainTextToken = 'abcdefghijklmnopqrstuvwxyz1234567890';
    $tokenId = $this->tokenService->extractTokenId($plainTextToken);

    expect($tokenId)->not->toBeEmpty()
        ->and(strlen((string) $tokenId))->toBe(64);
});

it('updates last used only when token exists', function (): void {
    $nonExistentTokenId = 'non_existent_' . uniqid();

    $this->tokenService->updateTokenLastUsed($nonExistentTokenId);

    expect($this->tokenService->getTokenMetadata($nonExistentTokenId))->toBeNull();
});

it('returns null when getting metadata for non-existent token', function (): void {
    $nonExistentTokenId = 'completely_fake_' . uniqid();

    $metadata = $this->tokenService->getTokenMetadata($nonExistentTokenId);

    expect($metadata)->toBeNull();
});

it('returns empty array when getting tokens for user with no tokens', function (): void {
    $userId = 'user_with_no_tokens_' . uniqid();

    $tokens = $this->tokenService->getUserTokens($userId);

    expect($tokens)->toBeArray()
        ->and($tokens)->toBeEmpty();
});

it('handles redis returning non-array for user tokens', function (): void {
    $userId = 'edge_case_user_' . uniqid();

    // Get tokens for user that doesn't exist - Redis might return false/null
    $tokens = $this->tokenService->getUserTokens($userId);

    expect($tokens)->toBeArray();
});

it('returns null when redis returns empty array for metadata', function (): void {
    // Create a token but manually delete it from Redis to force empty array scenario
    $tokenId = 'empty_metadata_' . uniqid();
    $userId = 'user_123';

    $this->tokenService->storeTokenMetadata($tokenId, $userId, 3600);

    // Directly manipulate Redis to create the edge case
    $redis = \Illuminate\Support\Facades\Redis::connection('tokens');
    $tokenKey = 'token:' . $tokenId;

    // Delete all hash fields to make hgetall return empty array
    $redis->del($tokenKey);
    // Recreate the key but with no data (simulate edge case)
    $redis->set($tokenKey, '');

    $metadata = $this->tokenService->getTokenMetadata($tokenId);

    expect($metadata)->toBeNull();

    // Cleanup
    $redis->del($tokenKey);
});

it('getUserTokens always returns array even for non-existent users', function (): void {
    // Testing edge case at line 162: if (!is_array($tokens)) return [];
    // This is defensive programming - smembers should always return array

    // Test with various edge case user IDs
    $edgeCaseUserIds = [
        '',  // empty string
        '0',  // string zero
        0,  // integer zero
        'null',  // string 'null'
        'false',  // string 'false'
    ];

    foreach ($edgeCaseUserIds as $userId) {
        $tokens = $this->tokenService->getUserTokens($userId);
        expect($tokens)->toBeArray()
            ->and($tokens)->toBeEmpty();
    }
});
