<?php

use App\Models\Article;
use App\Models\Comment;
use App\Models\User;

beforeEach(function (): void {
    Comment::query()->delete();
    Article::query()->delete();
    User::query()->delete();

    $this->user = User::factory()->create();
    $this->article = Article::factory()->create(['author_id' => $this->user->id, 'comment_count' => 0]);
});

describe('CommentObserver Unit Tests', function (): void {
    it('updates comment count when comment is restored', function (): void {
        $comment = Comment::factory()->create([
            'article_id' => $this->article->id,
            'user_id' => $this->user->id,
        ]);

        $comment->delete();

        expect($this->article->fresh()->comment_count)->toBe(0);

        $comment->restore();

        expect($this->article->fresh()->comment_count)->toBe(1);
    });

    it('updates comment count when comment is force deleted', function (): void {
        $comment = Comment::factory()->create([
            'article_id' => $this->article->id,
            'user_id' => $this->user->id,
        ]);

        expect($this->article->fresh()->comment_count)->toBe(1);

        $comment->forceDelete();

        expect($this->article->fresh()->comment_count)->toBe(0);
    });
});
