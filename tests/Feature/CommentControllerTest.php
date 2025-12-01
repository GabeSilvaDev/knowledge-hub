<?php

use App\Models\Article;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Http\JsonResponse;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\deleteJson;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\putJson;

const COMMENTS_ENDPOINT = '/api/comments';

beforeEach(function (): void {
    Comment::query()->delete();
    Article::query()->delete();
    User::query()->delete();

    $this->user = User::factory()->create();
    $this->article = Article::factory()->create(['author_id' => $this->user->id]);
});

describe('CommentController Feature Tests', function (): void {
    it('returns all comments for an article', function (): void {
        Comment::factory()->count(5)->create(['article_id' => $this->article->id]);

        $response = getJson("/api/articles/{$this->article->id}/comments");

        $response->assertStatus(JsonResponse::HTTP_OK)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => ['id', 'article_id', 'user_id', 'content', 'user'],
                ],
            ]);
    });

    it('creates a new comment when authenticated', function (): void {
        actingAs($this->user);

        $commentData = [
            'article_id' => $this->article->id,
            'content' => 'This is a great article!',
        ];

        $response = postJson(COMMENTS_ENDPOINT, $commentData);

        $response->assertStatus(JsonResponse::HTTP_CREATED)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['id', 'article_id', 'user_id', 'content', 'user'],
            ]);
    });

    it('updates a comment when user is the owner', function (): void {
        actingAs($this->user);

        $comment = Comment::factory()->create([
            'article_id' => $this->article->id,
            'user_id' => $this->user->id,
        ]);

        $response = putJson(COMMENTS_ENDPOINT . "/{$comment->id}", [
            'content' => 'Updated content',
        ]);

        $response->assertStatus(JsonResponse::HTTP_OK)
            ->assertJson([
                'success' => true,
                'message' => 'Comentário atualizado com sucesso.',
            ]);
    });

    it('prevents updating comment by non-owner', function (): void {
        $otherUser = User::factory()->create();
        actingAs($otherUser);

        $comment = Comment::factory()->create([
            'article_id' => $this->article->id,
            'user_id' => $this->user->id,
        ]);

        $response = putJson(COMMENTS_ENDPOINT . "/{$comment->id}", [
            'content' => 'Updated content',
        ]);

        $response->assertStatus(JsonResponse::HTTP_FORBIDDEN)
            ->assertJson([
                'success' => false,
                'message' => 'Você não tem permissão para editar este comentário.',
            ]);
    });

    it('deletes a comment when user is the owner', function (): void {
        actingAs($this->user);

        $comment = Comment::factory()->create([
            'article_id' => $this->article->id,
            'user_id' => $this->user->id,
        ]);

        $response = deleteJson(COMMENTS_ENDPOINT . "/{$comment->id}");

        $response->assertStatus(JsonResponse::HTTP_OK)
            ->assertJson([
                'success' => true,
                'message' => 'Comentário excluído com sucesso.',
            ]);
    });

    it('prevents deleting comment by non-owner', function (): void {
        $otherUser = User::factory()->create();
        actingAs($otherUser);

        $comment = Comment::factory()->create([
            'article_id' => $this->article->id,
            'user_id' => $this->user->id,
        ]);

        $response = deleteJson(COMMENTS_ENDPOINT . "/{$comment->id}");

        $response->assertStatus(JsonResponse::HTTP_FORBIDDEN)
            ->assertJson([
                'success' => false,
                'message' => 'Você não tem permissão para excluir este comentário.',
            ]);
    });
});
