<?php

use App\ValueObjects\ArticleContent;
use App\ValueObjects\Content;
use App\ValueObjects\Slug;
use App\ValueObjects\Title;

const TEST_TITLE = 'Test Title';
const SAME_TITLE = 'Same Title';
const SAME_CONTENT = 'Same content.';

describe('ArticleContent', function (): void {

    describe('create method', function (): void {
        it('creates ArticleContent with all properties', function (): void {
            $title = Title::from(TEST_TITLE);
            $content = Content::from('This is test content for the article.');
            $slug = Slug::from('test-title');

            $articleContent = ArticleContent::create($title, $content, $slug);

            expect($articleContent)->toBeInstanceOf(ArticleContent::class)
                ->and($articleContent->getTitle())->toBe($title)
                ->and($articleContent->getContent())->toBe($content)
                ->and($articleContent->getSlug())->toBe($slug);
        });

        it('creates ArticleContent without slug', function (): void {
            $title = Title::from('Test Title Without Slug');
            $content = Content::from('This is test content for the article without slug.');

            $articleContent = ArticleContent::create($title, $content);

            expect($articleContent)->toBeInstanceOf(ArticleContent::class)
                ->and($articleContent->getTitle())->toBe($title)
                ->and($articleContent->getContent())->toBe($content)
                ->and($articleContent->getSlug())->toBeNull();
        });
    });

    describe('constructor', function (): void {
        it('creates ArticleContent via constructor', function (): void {
            $title = Title::from('Constructor Test');
            $content = Content::from('Content via constructor.');
            $slug = Slug::from('constructor-test');

            $articleContent = new ArticleContent($title, $content, $slug);

            expect($articleContent)->toBeInstanceOf(ArticleContent::class)
                ->and($articleContent->getTitle())->toBe($title)
                ->and($articleContent->getContent())->toBe($content)
                ->and($articleContent->getSlug())->toBe($slug);
        });
    });

    describe('getGeneratedSlug method', function (): void {
        it('returns existing slug when provided', function (): void {
            $title = Title::from(TEST_TITLE);
            $content = Content::from('Test content.');
            $slug = Slug::from('custom-slug');

            $articleContent = ArticleContent::create($title, $content, $slug);

            expect($articleContent->getGeneratedSlug())->toBe($slug);
        });

        it('generates slug from title when not provided', function (): void {
            $title = Title::from('Test Title For Auto Generation');
            $content = Content::from('Test content for auto generation.');

            $articleContent = ArticleContent::create($title, $content);

            $generatedSlug = $articleContent->getGeneratedSlug();

            expect($generatedSlug)->toBeInstanceOf(Slug::class)
                ->and($generatedSlug->getValue())->toBe('test-title-for-auto-generation');
        });
    });

    describe('getExcerpt method', function (): void {
        it('returns excerpt with default length', function (): void {
            $title = Title::from(TEST_TITLE);
            $longContent = str_repeat('This is a long content. ', 20);
            $content = Content::from($longContent);

            $articleContent = ArticleContent::create($title, $content);

            $excerpt = $articleContent->getExcerpt();

            expect($excerpt)->toBeString()
                ->and($excerpt)->toEndWith('...');
        });

        it('returns excerpt with custom length', function (): void {
            $title = Title::from(TEST_TITLE);
            $content = Content::from('This is a short content for testing custom excerpt length.');

            $articleContent = ArticleContent::create($title, $content);

            $excerpt = $articleContent->getExcerpt(30);

            expect($excerpt)->toBeString()
                ->and(strlen($excerpt))->toBeLessThanOrEqual(33);
        });
    });

    describe('getReadingTime method', function (): void {
        it('returns reading time from content', function (): void {
            $title = Title::from(TEST_TITLE);
            $content = Content::from(str_repeat('word ', 300));

            $articleContent = ArticleContent::create($title, $content);

            $readingTime = $articleContent->getReadingTime();

            expect($readingTime)->toBeInt()
                ->and($readingTime)->toBeGreaterThan(0);
        });
    });

    describe('equals method', function (): void {
        it('returns true for identical ArticleContent objects', function (): void {
            $title = Title::from(SAME_TITLE);
            $content = Content::from(SAME_CONTENT);
            $slug = Slug::from('same-slug');

            $articleContent1 = ArticleContent::create($title, $content, $slug);
            $articleContent2 = ArticleContent::create($title, $content, $slug);

            expect($articleContent1->equals($articleContent2))->toBeTrue();
        });

        it('returns false for different titles', function (): void {
            $title1 = Title::from('Different Title 1');
            $title2 = Title::from('Different Title 2');
            $content = Content::from(SAME_CONTENT);
            $slug = Slug::from('same-slug');

            $articleContent1 = ArticleContent::create($title1, $content, $slug);
            $articleContent2 = ArticleContent::create($title2, $content, $slug);

            expect($articleContent1->equals($articleContent2))->toBeFalse();
        });

        it('returns false for different content', function (): void {
            $title = Title::from(SAME_TITLE);
            $content1 = Content::from('Different content 1.');
            $content2 = Content::from('Different content 2.');
            $slug = Slug::from('same-slug');

            $articleContent1 = ArticleContent::create($title, $content1, $slug);
            $articleContent2 = ArticleContent::create($title, $content2, $slug);

            expect($articleContent1->equals($articleContent2))->toBeFalse();
        });

        it('returns true when both have null slugs', function (): void {
            $title = Title::from(SAME_TITLE);
            $content = Content::from(SAME_CONTENT);

            $articleContent1 = ArticleContent::create($title, $content);
            $articleContent2 = ArticleContent::create($title, $content);

            expect($articleContent1->equals($articleContent2))->toBeTrue();
        });

        it('returns false when one has slug and other does not', function (): void {
            $title = Title::from(SAME_TITLE);
            $content = Content::from(SAME_CONTENT);
            $slug = Slug::from('test-slug');

            $articleContent1 = ArticleContent::create($title, $content, $slug);
            $articleContent2 = ArticleContent::create($title, $content);

            expect($articleContent1->equals($articleContent2))->toBeFalse();
        });

        it('returns false for different slugs', function (): void {
            $title = Title::from(SAME_TITLE);
            $content = Content::from(SAME_CONTENT);
            $slug1 = Slug::from('different-slug-1');
            $slug2 = Slug::from('different-slug-2');

            $articleContent1 = ArticleContent::create($title, $content, $slug1);
            $articleContent2 = ArticleContent::create($title, $content, $slug2);

            expect($articleContent1->equals($articleContent2))->toBeFalse();
        });

        it('returns true for same slugs', function (): void {
            $title = Title::from(SAME_TITLE);
            $content = Content::from(SAME_CONTENT);
            $slug1 = Slug::from('same-slug');
            $slug2 = Slug::from('same-slug');

            $articleContent1 = ArticleContent::create($title, $content, $slug1);
            $articleContent2 = ArticleContent::create($title, $content, $slug2);

            expect($articleContent1->equals($articleContent2))->toBeTrue();
        });
    });
});
