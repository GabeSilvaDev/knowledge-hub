<?php

use App\Contracts\AuthServiceInterface;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\mock;
use function Pest\Laravel\postJson;

beforeEach(function () {
    mock(AuthServiceInterface::class);
});

describe('POST /api/register', function () {
    test('returns created response on successful registration', function () {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
            'username' => 'testuser',
        ];

        $validatedUserData = $userData;
        unset($validatedUserData['password_confirmation']);

        $user = User::factory()->make($validatedUserData);

        app(AuthServiceInterface::class)
            ->shouldReceive('register')
            ->once()
            ->with($validatedUserData)
            ->andReturn(['user' => $user, 'token' => 'test-token']);

        postJson('/api/register', $userData)
            ->assertStatus(JsonResponse::HTTP_CREATED)
            ->assertJson([['token' => 'test-token']]);
    });
});

describe('POST /api/login', function () {
    test('returns ok response on successful login', function () {
        $credentials = [
            'email' => 'test@example.com',
            'password' => 'password',
        ];

        $user = User::factory()->make(['email' => $credentials['email']]);

        app(AuthServiceInterface::class)
            ->shouldReceive('login')
            ->once()
            ->with($credentials['email'], $credentials['password'])
            ->andReturn(['user' => $user, 'token' => 'test-token']);

        postJson('/api/login', $credentials)
            ->assertStatus(JsonResponse::HTTP_OK)
            ->assertJson([['token' => 'test-token']]);
    });
});

describe('POST /api/logout', function () {
    test('returns ok response on successful logout', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        app(AuthServiceInterface::class)->shouldReceive('logout')->once();

        postJson('/api/logout')
            ->assertStatus(JsonResponse::HTTP_OK)
            ->assertJson(['message' => 'Logged out successfully']);
    });
});

describe('POST /api/revoke-all', function () {
    test('returns ok response on successful token revocation', function () {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        app(AuthServiceInterface::class)->shouldReceive('revokeAllTokens')->once();

        postJson('/api/revoke-all')
            ->assertStatus(JsonResponse::HTTP_OK)
            ->assertJson(['message' => 'All tokens revoked successfully']);
    });
});