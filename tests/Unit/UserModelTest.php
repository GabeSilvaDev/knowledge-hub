<?php

use App\Models\Article;
use App\Models\Comment;
use App\Models\Follower;
use App\Models\Like;
use App\Models\User;

beforeEach(function (): void {
    Follower::query()->delete();
    Like::query()->delete();
    Comment::query()->delete();
    Article::query()->delete();
    User::query()->delete();

    $this->user = User::factory()->create();
});

describe('User Model Unit Tests', function (): void {
    it('has articles relationship', function (): void {
        Article::factory()->count(3)->create(['author_id' => $this->user->id]);

        expect($this->user->articles)->toHaveCount(3);
    });

    it('has comments relationship', function (): void {
        $article = Article::factory()->create(['author_id' => $this->user->id]);

        Comment::factory()->count(4)->create([
            'article_id' => $article->id,
            'user_id' => $this->user->id,
        ]);

        expect($this->user->comments)->toHaveCount(4);
    });

    it('has likes relationship', function (): void {
        $articles = Article::factory()->count(5)->create(['author_id' => $this->user->id]);

        foreach ($articles as $article) {
            Like::factory()->create([
                'article_id' => $article->id,
                'user_id' => $this->user->id,
            ]);
        }

        expect($this->user->likes)->toHaveCount(5);
    });

    it('has following relationship', function (): void {
        $otherUsers = User::factory()->count(3)->create();

        foreach ($otherUsers as $otherUser) {
            Follower::create([
                'follower_id' => $this->user->id,
                'following_id' => $otherUser->id,
            ]);
        }

        expect($this->user->following)->toHaveCount(3);
    });

    it('has followers relationship', function (): void {
        $otherUsers = User::factory()->count(2)->create();

        foreach ($otherUsers as $otherUser) {
            Follower::create([
                'follower_id' => $otherUser->id,
                'following_id' => $this->user->id,
            ]);
        }

        expect($this->user->followers)->toHaveCount(2);
    });

    it('has tokens relationship', function (): void {
        $token = $this->user->createToken('test-token');

        expect($this->user->tokens)->toHaveCount(1)
            ->and($this->user->tokens->first()->name)->toBe('test-token');
    });
});
