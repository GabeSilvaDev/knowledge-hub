<?php

use App\Models\Follower;
use App\Models\User;
use Illuminate\Http\JsonResponse;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

beforeEach(function (): void {
    Follower::query()->delete();
    User::query()->delete();

    $this->user = User::factory()->create();
    $this->targetUser = User::factory()->create();
});

describe('FollowerController Feature Tests', function (): void {
    it('toggles follow for a user (follow action)', function (): void {
        actingAs($this->user);

        $response = postJson("/api/users/{$this->targetUser->id}/follow");

        $response->assertStatus(JsonResponse::HTTP_OK)
            ->assertJson([
                'success' => true,
                'message' => 'User followed successfully.',
            ]);

        expect(Follower::count())->toBe(1);
    });

    it('toggles follow for a user (unfollow action)', function (): void {
        actingAs($this->user);

        Follower::create([
            'follower_id' => $this->user->id,
            'following_id' => $this->targetUser->id,
        ]);

        $response = postJson("/api/users/{$this->targetUser->id}/follow");

        $response->assertStatus(JsonResponse::HTTP_OK)
            ->assertJson([
                'success' => true,
                'message' => 'You have unfollowed this user.',
            ]);

        expect(Follower::count())->toBe(0);
    });

    it('returns followers list', function (): void {
        Follower::create([
            'follower_id' => $this->user->id,
            'following_id' => $this->targetUser->id,
        ]);

        $response = getJson("/api/users/{$this->targetUser->id}/followers");

        $response->assertStatus(JsonResponse::HTTP_OK)
            ->assertJsonStructure([
                'success',
                'data',
            ]);
    });

    it('returns following list', function (): void {
        Follower::create([
            'follower_id' => $this->user->id,
            'following_id' => $this->targetUser->id,
        ]);

        $response = getJson("/api/users/{$this->user->id}/following");

        $response->assertStatus(JsonResponse::HTTP_OK)
            ->assertJsonStructure([
                'success',
                'data',
            ]);
    });

    it('checks follow status', function (): void {
        actingAs($this->user);

        Follower::create([
            'follower_id' => $this->user->id,
            'following_id' => $this->targetUser->id,
        ]);

        $response = getJson("/api/users/{$this->targetUser->id}/follow/check");

        $response->assertStatus(JsonResponse::HTTP_OK)
            ->assertJson([
                'success' => true,
                'data' => [
                    'is_following' => true,
                ],
            ]);
    });

    it('returns bad request when trying to follow self', function (): void {
        actingAs($this->user);

        $response = postJson("/api/users/{$this->user->id}/follow");

        $response->assertStatus(JsonResponse::HTTP_BAD_REQUEST)
            ->assertJson([
                'success' => false,
            ]);
    });
});
