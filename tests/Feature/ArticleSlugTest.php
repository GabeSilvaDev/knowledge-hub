<?php

use App\Models\Article;
use App\Models\User;
use App\Services\ArticleService;

beforeEach(function (): void {
    Article::query()->delete();
    User::query()->delete();

    $this->user = User::factory()->create();
    $this->articleService = app(ArticleService::class);
});

describe('Article Slug Generation Tests', function (): void {
    it('generates unique slug when updating to duplicate title', function (): void {
        Article::factory()->create([
            'title' => 'Test Article',
            'slug' => 'test-article',
            'author_id' => $this->user->id,
        ]);

        $article = Article::factory()->create([
            'title' => 'Different Article',
            'slug' => 'different-article',
            'author_id' => $this->user->id,
        ]);

        $updatedArticle = $this->articleService->updateArticle($article, [
            'title' => 'Test Article',
        ]);

        expect($updatedArticle->slug)->toBe('test-article-1');
    });

    it('generates unique slug with incrementing counter on update', function (): void {
        Article::factory()->create([
            'slug' => 'test-article',
            'author_id' => $this->user->id,
        ]);

        Article::factory()->create([
            'slug' => 'test-article-1',
            'author_id' => $this->user->id,
        ]);

        $article = Article::factory()->create([
            'title' => 'Different Article',
            'slug' => 'different-article',
            'author_id' => $this->user->id,
        ]);

        $updatedArticle = $this->articleService->updateArticle($article, [
            'title' => 'Test Article',
        ]);

        expect($updatedArticle->slug)->toBe('test-article-2');
    });

    it('does not change slug when updating without title change', function (): void {
        $article = Article::factory()->create([
            'title' => 'Original Title',
            'slug' => 'original-title',
            'content' => 'Original content',
            'author_id' => $this->user->id,
        ]);

        $updatedArticle = $this->articleService->updateArticle($article, [
            'content' => 'Updated content',
        ]);

        expect($updatedArticle->slug)->toBe('original-title');
    });

    it('keeps same slug when updating with provided slug', function (): void {
        $article = Article::factory()->create([
            'title' => 'Original Title',
            'slug' => 'custom-slug',
            'author_id' => $this->user->id,
        ]);

        $updatedArticle = $this->articleService->updateArticle($article, [
            'title' => 'New Title',
            'slug' => 'custom-slug',
        ]);

        expect($updatedArticle->slug)->toBe('custom-slug');
    });
});
