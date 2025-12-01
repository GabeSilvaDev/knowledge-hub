<?php

use App\Models\Article;
use App\Models\Comment;
use App\Models\User;

beforeEach(function (): void {
    Comment::query()->delete();
    Article::query()->delete();
    User::query()->delete();

    $this->user = User::factory()->create();
    $this->article = Article::factory()->create(['author_id' => $this->user->id]);
    $this->comment = Comment::factory()->create([
        'article_id' => $this->article->id,
        'user_id' => $this->user->id,
    ]);
});

describe('Comment Model Unit Tests', function (): void {
    it('has article relationship', function (): void {
        $article = $this->comment->article;

        expect($article)->not->toBeNull()
            ->and($article->id)->toBe($this->article->id);
    });

    it('has user relationship', function (): void {
        $user = $this->comment->user;

        expect($user)->not->toBeNull()
            ->and($user->id)->toBe($this->user->id);
    });
});
