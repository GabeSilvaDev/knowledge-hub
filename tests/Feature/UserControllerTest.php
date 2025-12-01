<?php

use App\Models\Article;
use App\Models\User;
use Illuminate\Http\JsonResponse;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;
use function Pest\Laravel\putJson;

beforeEach(function (): void {
    Article::query()->delete();
    User::query()->delete();

    $this->user = User::factory()->create([
        'name' => 'John Doe',
        'username' => 'johndoe',
        'bio' => 'Test bio',
        'avatar_url' => 'https://example.com/avatar.jpg',
    ]);
});

describe('UserController Feature Tests', function (): void {
    it('shows public profile without authentication (limited to 10 articles)', function (): void {
        Article::factory()->count(15)->create(['author_id' => $this->user->id, 'status' => 'published']);

        $response = getJson("/api/users/{$this->user->id}");

        $response->assertStatus(JsonResponse::HTTP_OK)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'user',
                    'articles',
                    'articles_count',
                    'followers_count',
                    'following_count',
                    'is_following',
                    'limited',
                ],
            ])
            ->assertJson([
                'data' => [
                    'limited' => true,
                ],
            ]);
    });

    it('shows public profile with authentication (unlimited articles)', function (): void {
        actingAs($this->user);

        $targetUser = User::factory()->create();
        Article::factory()->count(15)->create(['author_id' => $targetUser->id, 'status' => 'published']);

        $response = getJson("/api/users/{$targetUser->id}");

        $response->assertStatus(JsonResponse::HTTP_OK)
            ->assertJson([
                'data' => [
                    'limited' => false,
                ],
            ]);
    });

    it('returns current user with me endpoint', function (): void {
        actingAs($this->user);

        $response = getJson('/api/me');

        $response->assertStatus(JsonResponse::HTTP_OK)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'user',
                    'followers_count',
                    'following_count',
                    'articles_count',
                ],
            ]);
    });

    it('updates authenticated user profile', function (): void {
        actingAs($this->user);

        $response = putJson('/api/me', [
            'name' => 'Jane Doe',
            'bio' => 'Updated bio',
        ]);

        $response->assertStatus(JsonResponse::HTTP_OK)
            ->assertJson([
                'success' => true,
                'message' => 'Profile updated successfully.',
            ]);
    });

    it('returns bad request when updating with no data', function (): void {
        actingAs($this->user);

        $response = putJson('/api/me', []);

        $response->assertStatus(JsonResponse::HTTP_BAD_REQUEST)
            ->assertJson([
                'success' => false,
                'message' => 'No data to update.',
            ]);
    });

    it('checks if following another user when authenticated', function (): void {
        $currentUser = User::factory()->create();
        $targetUser = User::factory()->create();

        actingAs($currentUser);

        $response = getJson("/api/users/{$targetUser->id}");

        $response->assertStatus(JsonResponse::HTTP_OK)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'is_following',
                ],
            ]);
    });

    it('does not check following when viewing own profile', function (): void {
        actingAs($this->user);

        $response = getJson("/api/users/{$this->user->id}");

        $response->assertStatus(JsonResponse::HTTP_OK)
            ->assertJson([
                'data' => [
                    'is_following' => false,
                ],
            ]);
    });
});
