<?php

use App\Models\Article;
use App\Models\Comment;
use App\Models\Like;
use App\Models\User;

beforeEach(function (): void {
    Like::query()->delete();
    Comment::query()->delete();
    Article::query()->delete();
    User::query()->delete();

    $this->user = User::factory()->create();
    $this->article = Article::factory()->create(['author_id' => $this->user->id]);
});

describe('Article Model Unit Tests', function (): void {
    it('has comments relationship', function (): void {
        Comment::factory()->count(3)->create([
            'article_id' => $this->article->id,
            'user_id' => $this->user->id,
        ]);

        expect($this->article->comments)->toHaveCount(3);
    });

    it('has likes relationship', function (): void {
        $users = User::factory()->count(5)->create();

        foreach ($users as $user) {
            Like::factory()->create([
                'article_id' => $this->article->id,
                'user_id' => $user->id,
            ]);
        }

        expect($this->article->likes)->toHaveCount(5);
    });

    it('returns searchable array', function (): void {
        $searchable = $this->article->toSearchableArray();

        expect($searchable)->toBeArray()
            ->toHaveKeys(['id', 'title', 'slug', 'content', 'excerpt', 'author_id', 'status', 'type', 'tags', 'categories', 'published_at', 'created_at']);
    });

    it('returns scout key as string', function (): void {
        $scoutKey = $this->article->getScoutKey();

        expect($scoutKey)->toBeString()
            ->toBe($this->article->id);
    });

    it('filters articles by tags scope', function (): void {
        Article::factory()->create([
            'author_id' => $this->user->id,
            'tags' => ['laravel', 'php'],
        ]);

        Article::factory()->create([
            'author_id' => $this->user->id,
            'tags' => ['javascript', 'react'],
        ]);

        $articles = Article::tags('laravel')->get();

        expect($articles)->toHaveCount(1);
    });

    it('filters articles by multiple tags', function (): void {
        Article::factory()->create([
            'author_id' => $this->user->id,
            'tags' => ['laravel', 'php'],
        ]);

        Article::factory()->create([
            'author_id' => $this->user->id,
            'tags' => ['vue', 'javascript'],
        ]);

        Article::factory()->create([
            'author_id' => $this->user->id,
            'tags' => ['react', 'typescript'],
        ]);

        $articles = Article::tags(['laravel', 'vue'])->get();

        expect($articles)->toHaveCount(2);
    });

    it('filters articles by categories scope', function (): void {
        Article::factory()->create([
            'author_id' => $this->user->id,
            'categories' => ['tutorial', 'backend'],
        ]);

        Article::factory()->create([
            'author_id' => $this->user->id,
            'categories' => ['news', 'frontend'],
        ]);

        $articles = Article::categories('tutorial')->get();

        expect($articles)->toHaveCount(1);
    });

    it('filters articles by multiple categories', function (): void {
        Article::factory()->create([
            'author_id' => $this->user->id,
            'categories' => ['tutorial', 'backend'],
        ]);

        Article::factory()->create([
            'author_id' => $this->user->id,
            'categories' => ['news', 'frontend'],
        ]);

        Article::factory()->create([
            'author_id' => $this->user->id,
            'categories' => ['guide', 'fullstack'],
        ]);

        $articles = Article::categories(['tutorial', 'news'])->get();

        expect($articles)->toHaveCount(2);
    });
});
