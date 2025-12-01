<?php

use App\Models\Article;
use App\Models\Follower;
use App\Models\User;
use Illuminate\Http\JsonResponse;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

beforeEach(function (): void {
    Follower::query()->delete();
    Article::query()->delete();
    User::query()->delete();

    $this->user = User::factory()->create();
});

describe('FeedController Feature Tests', function (): void {
    it('returns public feed for unauthenticated users', function (): void {
        $author = User::factory()->create();

        Article::factory()->count(5)->create([
            'author_id' => $author->id,
            'status' => 'published',
        ]);

        $response = getJson('/api/feed');

        $response->assertStatus(JsonResponse::HTTP_OK)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
            ]);
    });

    it('returns personalized feed for authenticated users', function (): void {
        actingAs($this->user);

        $author = User::factory()->create();
        Follower::create([
            'follower_id' => $this->user->id,
            'following_id' => $author->id,
        ]);

        Article::factory()->count(3)->create([
            'author_id' => $author->id,
            'status' => 'published',
        ]);

        $response = getJson('/api/feed');

        $response->assertStatus(JsonResponse::HTTP_OK)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
            ]);
    });

    it('returns public feed explicitly', function (): void {
        $author = User::factory()->create();

        Article::factory()->count(5)->create([
            'author_id' => $author->id,
            'status' => 'published',
        ]);

        $response = getJson('/api/feed/public');

        $response->assertStatus(JsonResponse::HTTP_OK)
            ->assertJsonStructure([
                'success',
                'data',
            ]);
    });

    it('returns personalized feed via explicit route', function (): void {
        actingAs($this->user);

        $author = User::factory()->create();
        Follower::create([
            'follower_id' => $this->user->id,
            'following_id' => $author->id,
        ]);

        Article::factory()->count(3)->create([
            'author_id' => $author->id,
            'status' => 'published',
        ]);

        $response = getJson('/api/feed/personalized');

        $response->assertStatus(JsonResponse::HTTP_OK)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
            ]);
    });

    it('requires authentication for personalized feed route', function (): void {
        $response = getJson('/api/feed/personalized');

        $response->assertStatus(JsonResponse::HTTP_UNAUTHORIZED);
    });
});
