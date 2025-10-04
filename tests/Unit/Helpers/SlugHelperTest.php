<?php

use App\Helpers\SlugHelper;
use App\Models\Article;
use App\Models\User;

const ARTICLE_1_TITLE = 'Article 1';
const ARTICLE_2_TITLE = 'Article 2';
const ARTICLE_3_TITLE = 'Article 3';

describe('SlugHelper', function (): void {

    afterEach(function (): void {
        Article::query()->delete();
    });

    describe('generateUniqueSlug method', function (): void {
        it('generates basic slug from title', function (): void {
            $slug = SlugHelper::generateUniqueSlug('Test Article Title', Article::class);

            expect($slug)->toBe('test-article-title');
        });

        it('generates unique slug when original exists', function (): void {
            $user = User::factory()->create();
            Article::factory()->create([
                'title' => 'Existing Article',
                'slug' => 'test-slug',
                'author_id' => $user->_id,
            ]);

            $slug = SlugHelper::generateUniqueSlug('Test Slug', Article::class);

            expect($slug)->toBe('test-slug-1');
        });

        it('generates incremental unique slugs when multiple exist', function (): void {
            $user = User::factory()->create();
            Article::factory()->create([
                'title' => ARTICLE_1_TITLE,
                'slug' => 'same-title',
                'author_id' => $user->_id,
            ]);
            Article::factory()->create([
                'title' => ARTICLE_2_TITLE,
                'slug' => 'same-title-1',
                'author_id' => $user->_id,
            ]);
            Article::factory()->create([
                'title' => ARTICLE_3_TITLE,
                'slug' => 'same-title-2',
                'author_id' => $user->_id,
            ]);

            $slug = SlugHelper::generateUniqueSlug('Same Title', Article::class);

            expect($slug)->toBe('same-title-3');
        });

        it('excludes specific ID when generating unique slug', function (): void {
            $user = User::factory()->create();
            $article1 = Article::factory()->create([
                'title' => ARTICLE_1_TITLE,
                'slug' => 'update-test',
                'author_id' => $user->_id,
            ]);
            Article::factory()->create([
                'title' => ARTICLE_2_TITLE,
                'slug' => 'update-test-1',
                'author_id' => $user->_id,
            ]);

            $slug = SlugHelper::generateUniqueSlug('Update Test', Article::class, $article1->_id);

            expect($slug)->toBe('update-test');
        });

        it('generates unique slug with exclusion when conflict exists', function (): void {
            $user = User::factory()->create();
            Article::factory()->create([
                'title' => ARTICLE_1_TITLE,
                'slug' => 'conflict-test',
                'author_id' => $user->_id,
            ]);
            $article2 = Article::factory()->create([
                'title' => ARTICLE_2_TITLE,
                'slug' => 'conflict-test-1',
                'author_id' => $user->_id,
            ]);

            $slug = SlugHelper::generateUniqueSlug('Conflict Test', Article::class, $article2->_id);

            expect($slug)->toBe('conflict-test-1');
        });

        it('handles special characters in title', function (): void {
            $slug = SlugHelper::generateUniqueSlug('Título com Àcentos é Çaracteres!', Article::class);

            expect($slug)->toBe('titulo-com-acentos-e-caracteres');
        });

        it('handles empty and whitespace titles', function (): void {
            $slug1 = SlugHelper::generateUniqueSlug('   ', Article::class);
            $slug2 = SlugHelper::generateUniqueSlug('', Article::class);

            expect($slug1)->toBeString()
                ->and($slug2)->toBeString();
        });

        it('handles very long titles', function (): void {
            $longTitle = str_repeat('Very Long Title With Many Words ', 10);
            $slug = SlugHelper::generateUniqueSlug($longTitle, Article::class);

            expect($slug)->toBeString()
                ->and($slug)->not->toBeEmpty();
        });

        it('generates different slugs for different model classes', function (): void {
            $slug1 = SlugHelper::generateUniqueSlug('Test Title', Article::class);
            $slug2 = SlugHelper::generateUniqueSlug('Test Title', User::class);

            expect($slug1)->toBe('test-title')
                ->and($slug2)->toBe('test-title');
        });

        it('handles numeric titles', function (): void {
            $slug = SlugHelper::generateUniqueSlug('123 456 789', Article::class);

            expect($slug)->toBe('123-456-789');
        });

        it('handles mixed case titles', function (): void {
            $slug = SlugHelper::generateUniqueSlug('MiXeD CaSe TiTlE', Article::class);

            expect($slug)->toBe('mixed-case-title');
        });
    });
});
