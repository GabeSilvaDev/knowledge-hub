<?php

use App\Models\Article;
use App\Models\Like;
use App\Models\User;

beforeEach(function (): void {
    Like::query()->delete();
    Article::query()->delete();
    User::query()->delete();

    $this->user = User::factory()->create();
    $this->article = Article::factory()->create(['author_id' => $this->user->id]);
    $this->like = Like::factory()->create([
        'article_id' => $this->article->id,
        'user_id' => $this->user->id,
    ]);
});

describe('Like Model Unit Tests', function (): void {
    it('has article relationship', function (): void {
        $article = $this->like->article;

        expect($article)->not->toBeNull()
            ->and($article->id)->toBe($this->article->id);
    });

    it('has user relationship', function (): void {
        $user = $this->like->user;

        expect($user)->not->toBeNull()
            ->and($user->id)->toBe($this->user->id);
    });
});
