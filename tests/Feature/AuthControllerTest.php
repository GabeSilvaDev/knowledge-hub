<?php

use App\Contracts\AuthServiceInterface;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\mock;
use function Pest\Laravel\postJson;

describe('POST /api/register', function (): void {
    beforeEach(function (): void {
        test()->authService = mock(AuthServiceInterface::class);
    });

    it('POST /api/register → returns created response on successful registration', function (): void {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'username' => 'johndoe',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
        ];

        $validatedUserData = array_diff_key($userData, ['password_confirmation' => '']);

        $user = User::factory()->make(['email' => $userData['email']]);

        test()->authService
            ->shouldReceive('register')
            ->once()
            ->with($validatedUserData)
            ->andReturn(['user' => $user, 'token' => 'test-token']);

        postJson('/api/register', $userData)
            ->assertStatus(JsonResponse::HTTP_CREATED)
            ->assertJson(['token' => 'test-token']);
    });
});

describe('POST /api/login', function (): void {
    beforeEach(function (): void {
        test()->authService = mock(AuthServiceInterface::class);
    });

    it('POST /api/login → returns ok response on successful login', function (): void {
        $credentials = [
            'email' => 'john@example.com',
            'password' => 'password123',
        ];

        $user = User::factory()->make(['email' => $credentials['email']]);

        test()->authService
            ->shouldReceive('login')
            ->once()
            ->with($credentials['email'], $credentials['password'])
            ->andReturn(['user' => $user, 'token' => 'test-token']);

        postJson('/api/login', $credentials)
            ->assertStatus(JsonResponse::HTTP_OK)
            ->assertJson(['token' => 'test-token']);
    });
});

describe('POST /api/logout', function (): void {
    test('returns ok response on successful logout', function (): void {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        mock(AuthServiceInterface::class)
            ->shouldReceive('logout')
            ->once()
            ->andReturn();

        postJson('/api/logout')
            ->assertStatus(JsonResponse::HTTP_OK)
            ->assertJson(['message' => 'Logged out successfully']);
    });
});

describe('POST /api/revoke-all', function (): void {
    test('returns ok response on successful token revocation', function (): void {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        mock(AuthServiceInterface::class)
            ->shouldReceive('revokeAllTokens')
            ->once();

        postJson('/api/revoke-all')
            ->assertStatus(JsonResponse::HTTP_OK)
            ->assertJson(['message' => 'All tokens revoked successfully']);
    });
});
