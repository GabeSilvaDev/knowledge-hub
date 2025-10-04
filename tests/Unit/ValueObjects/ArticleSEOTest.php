<?php

use App\ValueObjects\ArticleContent;
use App\ValueObjects\ArticleSEO;
use App\ValueObjects\Content;
use App\ValueObjects\Slug;
use App\ValueObjects\Title;

describe('ArticleSEO', function (): void {

    describe('constructor', function (): void {
        it('creates ArticleSEO with null values', function (): void {
            $seo = new ArticleSEO;

            expect($seo->getSeoTitle())->toBeNull()
                ->and($seo->getSeoDescription())->toBeNull()
                ->and($seo->getSeoKeywords())->toBeNull();
        });

        it('creates ArticleSEO with all values', function (): void {
            $title = Title::from('SEO Title');
            $description = 'This is a SEO description for the article.';
            $keywords = 'php, laravel, testing';

            $seo = new ArticleSEO($title, $description, $keywords);

            expect($seo->getSeoTitle())->toBe($title)
                ->and($seo->getSeoDescription())->toBe($description)
                ->and($seo->getSeoKeywords())->toBe($keywords);
        });

        it('creates ArticleSEO with partial values', function (): void {
            $title = Title::from('Partial SEO Title');

            $seo = new ArticleSEO($title, null, null);

            expect($seo->getSeoTitle())->toBe($title)
                ->and($seo->getSeoDescription())->toBeNull()
                ->and($seo->getSeoKeywords())->toBeNull();
        });
    });

    describe('create factory method', function (): void {
        it('creates ArticleSEO with default null values', function (): void {
            $seo = ArticleSEO::create();

            expect($seo->getSeoTitle())->toBeNull()
                ->and($seo->getSeoDescription())->toBeNull()
                ->and($seo->getSeoKeywords())->toBeNull();
        });

        it('creates ArticleSEO with custom values', function (): void {
            $title = Title::from('Custom SEO Title');
            $description = 'Custom SEO description with more details.';
            $keywords = 'custom, seo, keywords';

            $seo = ArticleSEO::create($title, $description, $keywords);

            expect($seo->getSeoTitle())->toBe($title)
                ->and($seo->getSeoDescription())->toBe($description)
                ->and($seo->getSeoKeywords())->toBe($keywords);
        });

        it('creates ArticleSEO with mixed null and non-null values', function (): void {
            $description = 'Only description provided.';

            $seo = ArticleSEO::create(null, $description, null);

            expect($seo->getSeoTitle())->toBeNull()
                ->and($seo->getSeoDescription())->toBe($description)
                ->and($seo->getSeoKeywords())->toBeNull();
        });
    });

    describe('fromContent factory method', function (): void {
        it('creates ArticleSEO from ArticleContent', function (): void {
            $title = Title::from('Article Title for SEO');
            $content = Content::from('This is a long content that will be used to generate SEO description automatically. It should be truncated to 160 characters for SEO purposes.');
            $slug = Slug::from('article-title-seo');

            $articleContent = ArticleContent::create($title, $content, $slug);
            $seo = ArticleSEO::fromContent($articleContent);

            expect($seo->getSeoTitle())->toBe($title)
                ->and($seo->getSeoDescription())->toBeString()
                ->and($seo->getSeoDescription())->not->toBeNull()
                ->and(strlen((string) $seo->getSeoDescription()))->toBeLessThanOrEqual(163)
                ->and($seo->getSeoKeywords())->toBeNull();
        });

        it('creates ArticleSEO from ArticleContent with short content', function (): void {
            $title = Title::from('Short Title');
            $content = Content::from('Short content.');

            $articleContent = ArticleContent::create($title, $content);
            $seo = ArticleSEO::fromContent($articleContent);

            expect($seo->getSeoTitle())->toBe($title)
                ->and($seo->getSeoDescription())->toBe('Short content.')
                ->and($seo->getSeoKeywords())->toBeNull();
        });
    });

    describe('isEmpty method', function (): void {
        it('returns true when all values are null', function (): void {
            $seo = ArticleSEO::create();

            expect($seo->isEmpty())->toBeTrue();
        });

        it('returns false when seo_title is provided', function (): void {
            $title = Title::from('SEO Title');
            $seo = ArticleSEO::create($title, null, null);

            expect($seo->isEmpty())->toBeFalse();
        });

        it('returns false when seo_description is provided', function (): void {
            $seo = ArticleSEO::create(null, 'SEO Description', null);

            expect($seo->isEmpty())->toBeFalse();
        });

        it('returns false when seo_keywords is provided', function (): void {
            $seo = ArticleSEO::create(null, null, 'seo, keywords');

            expect($seo->isEmpty())->toBeFalse();
        });

        it('returns false when all values are provided', function (): void {
            $title = Title::from('Full SEO Title');
            $seo = ArticleSEO::create($title, 'Full description', 'full, keywords');

            expect($seo->isEmpty())->toBeFalse();
        });
    });

    describe('equals method', function (): void {
        it('returns true for identical ArticleSEO objects', function (): void {
            $title = Title::from('Same SEO Title');
            $description = 'Same SEO description';
            $keywords = 'same, seo, keywords';

            $seo1 = ArticleSEO::create($title, $description, $keywords);
            $seo2 = ArticleSEO::create($title, $description, $keywords);

            expect($seo1->equals($seo2))->toBeTrue();
        });

        it('returns true when both have all null values', function (): void {
            $seo1 = ArticleSEO::create();
            $seo2 = ArticleSEO::create();

            expect($seo1->equals($seo2))->toBeTrue();
        });

        it('returns false for different seo_title', function (): void {
            $title1 = Title::from('Different Title 1');
            $title2 = Title::from('Different Title 2');

            $seo1 = ArticleSEO::create($title1, 'Same description', 'same, keywords');
            $seo2 = ArticleSEO::create($title2, 'Same description', 'same, keywords');

            expect($seo1->equals($seo2))->toBeFalse();
        });

        it('returns false when one has seo_title and other does not', function (): void {
            $title = Title::from('SEO Title');

            $seo1 = ArticleSEO::create($title, 'description', 'keywords');
            $seo2 = ArticleSEO::create(null, 'description', 'keywords');

            expect($seo1->equals($seo2))->toBeFalse();
        });

        it('returns false for different seo_description', function (): void {
            $title = Title::from('Same Title');

            $seo1 = ArticleSEO::create($title, 'Different description 1', 'same, keywords');
            $seo2 = ArticleSEO::create($title, 'Different description 2', 'same, keywords');

            expect($seo1->equals($seo2))->toBeFalse();
        });

        it('returns false for different seo_keywords', function (): void {
            $title = Title::from('Same Title');

            $seo1 = ArticleSEO::create($title, 'Same description', 'different, keywords, 1');
            $seo2 = ArticleSEO::create($title, 'Same description', 'different, keywords, 2');

            expect($seo1->equals($seo2))->toBeFalse();
        });

        it('returns true when both have same seo_title values', function (): void {
            $title1 = Title::from('Identical Title');
            $title2 = Title::from('Identical Title');

            $seo1 = ArticleSEO::create($title1, 'description', 'keywords');
            $seo2 = ArticleSEO::create($title2, 'description', 'keywords');

            expect($seo1->equals($seo2))->toBeTrue();
        });

        it('returns false when seo_description is null in one and not in other', function (): void {
            $title = Title::from('Same Title');

            $seo1 = ArticleSEO::create($title, null, 'keywords');
            $seo2 = ArticleSEO::create($title, 'description', 'keywords');

            expect($seo1->equals($seo2))->toBeFalse();
        });

        it('returns false when seo_keywords is null in one and not in other', function (): void {
            $title = Title::from('Same Title');

            $seo1 = ArticleSEO::create($title, 'description', null);
            $seo2 = ArticleSEO::create($title, 'description', 'keywords');

            expect($seo1->equals($seo2))->toBeFalse();
        });

        it('returns true when both have null seo_title', function (): void {
            $seo1 = ArticleSEO::create(null, 'description', 'keywords');
            $seo2 = ArticleSEO::create(null, 'description', 'keywords');

            expect($seo1->equals($seo2))->toBeTrue();
        });
    });
});
