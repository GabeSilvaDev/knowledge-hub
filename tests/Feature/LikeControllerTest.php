<?php

use App\Models\Article;
use App\Models\Like;
use App\Models\User;
use Illuminate\Http\JsonResponse;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

beforeEach(function (): void {
    Like::query()->delete();
    Article::query()->delete();
    User::query()->delete();

    $this->user = User::factory()->create();
    $this->article = Article::factory()->create();
});

describe('LikeController Feature Tests', function (): void {
    it('toggles like for an article (like action)', function (): void {
        actingAs($this->user);

        $response = postJson("/api/articles/{$this->article->id}/like");

        $response->assertStatus(JsonResponse::HTTP_OK)
            ->assertJson([
                'success' => true,
                'message' => 'Artigo curtido com sucesso.',
            ]);

        expect(Like::count())->toBe(1);
    });

    it('toggles like for an article (unlike action)', function (): void {
        actingAs($this->user);

        Like::create([
            'user_id' => $this->user->id,
            'article_id' => $this->article->id,
        ]);

        $response = postJson("/api/articles/{$this->article->id}/like");

        $response->assertStatus(JsonResponse::HTTP_OK)
            ->assertJson([
                'success' => true,
                'message' => 'Curtida removida com sucesso.',
            ]);

        expect(Like::count())->toBe(0);
    });

    it('checks if user has liked an article', function (): void {
        actingAs($this->user);

        Like::create([
            'user_id' => $this->user->id,
            'article_id' => $this->article->id,
        ]);

        $response = getJson("/api/articles/{$this->article->id}/like/check");

        $response->assertStatus(JsonResponse::HTTP_OK)
            ->assertJson([
                'success' => true,
                'data' => [
                    'has_liked' => true,
                ],
            ]);
    });
});
