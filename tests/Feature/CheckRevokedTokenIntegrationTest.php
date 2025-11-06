<?php

use App\Models\User;
use App\Services\TokenService;
use Illuminate\Support\Facades\Redis;

it('middleware updates token last used on actual http request', function (): void {
    $tokenService = app(TokenService::class);
    Redis::connection('tokens')->flushdb();

    $user = User::create([
        'name' => 'Integration Test User',
        'email' => 'integration' . uniqid() . '@test.com',
        'username' => 'integrationuser' . uniqid(),
        'password' => bcrypt('Password123'),
        'roles' => ['reader'],
    ]);

    $tokenResult = $tokenService->createToken($user, 'integration_test', 3600);
    $tokenId = $tokenService->extractTokenId($tokenResult->plainTextToken);

    $response = $this->withHeader('Authorization', 'Bearer ' . $tokenResult->plainTextToken)
        ->getJson('/api/articles');

    $response->assertStatus(200);

    $metadata = $tokenService->getTokenMetadata($tokenId);
    expect($metadata)->not->toBeNull()
        ->and($metadata)->toHaveKey('last_used_at');

    $user->tokens()->delete();
    $user->delete();
    Redis::connection('tokens')->flushdb();
});

it('middleware blocks revoked token on actual http request', function (): void {
    $tokenService = app(TokenService::class);
    Redis::connection('tokens')->flushdb();

    $user = User::create([
        'name' => 'Revoke Test User',
        'email' => 'revoke' . uniqid() . '@test.com',
        'username' => 'revokeuser' . uniqid(),
        'password' => bcrypt('Password123'),
        'roles' => ['reader'],
    ]);

    $tokenResult = $tokenService->createToken($user, 'revoke_test', 3600);
    $tokenId = $tokenService->extractTokenId($tokenResult->plainTextToken);

    $tokenService->revokeToken($tokenId);

    $response = $this->withHeader('Authorization', 'Bearer ' . $tokenResult->plainTextToken)
        ->getJson('/api/articles');

    $response->assertStatus(401)
        ->assertJson(['message' => 'Token has been revoked.']);

    $user->tokens()->delete();
    $user->delete();
    Redis::connection('tokens')->flushdb();
});
