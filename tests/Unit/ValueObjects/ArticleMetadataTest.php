<?php

use App\Enums\ArticleStatus;
use App\Enums\ArticleType;
use App\ValueObjects\ArticleMetadata;

const TEST_DATE = '2023-01-01';

function testArticleMetadataConstructor(): void
{
    describe('constructor', function (): void {
        it('creates ArticleMetadata with default values', function (): void {
            $metadata = new ArticleMetadata;

            expect($metadata->getStatus())->toBe(ArticleStatus::DRAFT)
                ->and($metadata->getType())->toBe(ArticleType::ARTICLE)
                ->and($metadata->isFeatured())->toBeFalse()
                ->and($metadata->isPinned())->toBeFalse()
                ->and($metadata->getPublishedAt())->toBeNull();
        });

        it('creates ArticleMetadata with custom values', function (): void {
            $publishedAt = new DateTime(TEST_DATE);

            $metadata = new ArticleMetadata(
                status: ArticleStatus::PUBLISHED,
                type: ArticleType::TUTORIAL,
                is_featured: true,
                is_pinned: true,
                published_at: $publishedAt
            );

            expect($metadata->getStatus())->toBe(ArticleStatus::PUBLISHED)
                ->and($metadata->getType())->toBe(ArticleType::TUTORIAL)
                ->and($metadata->isFeatured())->toBeTrue()
                ->and($metadata->isPinned())->toBeTrue()
                ->and($metadata->getPublishedAt())->toBe($publishedAt);
        });
    });
}

function testArticleMetadataCreateFactory(): void
{
    describe('create factory method', function (): void {
        it('creates ArticleMetadata with default values via factory', function (): void {
            $metadata = ArticleMetadata::create();

            expect($metadata->getStatus())->toBe(ArticleStatus::DRAFT)
                ->and($metadata->getType())->toBe(ArticleType::ARTICLE)
                ->and($metadata->isFeatured())->toBeFalse()
                ->and($metadata->isPinned())->toBeFalse()
                ->and($metadata->getPublishedAt())->toBeNull();
        });

        it('creates ArticleMetadata with custom values via factory', function (): void {
            $publishedAt = new DateTime('2023-06-15');

            $metadata = ArticleMetadata::create(
                status: ArticleStatus::PUBLISHED,
                type: ArticleType::WIKI,
                is_featured: true,
                is_pinned: false,
                published_at: $publishedAt
            );

            expect($metadata->getStatus())->toBe(ArticleStatus::PUBLISHED)
                ->and($metadata->getType())->toBe(ArticleType::WIKI)
                ->and($metadata->isFeatured())->toBeTrue()
                ->and($metadata->isPinned())->toBeFalse()
                ->and($metadata->getPublishedAt())->toBe($publishedAt);
        });
    });
}

function testArticleMetadataPublishedFactory(): void
{
    describe('published factory method', function (): void {
        it('creates published ArticleMetadata with default article type', function (): void {
            $metadata = ArticleMetadata::published();

            expect($metadata->getStatus())->toBe(ArticleStatus::PUBLISHED)
                ->and($metadata->getType())->toBe(ArticleType::ARTICLE)
                ->and($metadata->isFeatured())->toBeFalse()
                ->and($metadata->isPinned())->toBeFalse()
                ->and($metadata->getPublishedAt())->toBeInstanceOf(DateTime::class);
        });

        it('creates published ArticleMetadata with custom type', function (): void {
            $metadata = ArticleMetadata::published(ArticleType::TUTORIAL);

            expect($metadata->getStatus())->toBe(ArticleStatus::PUBLISHED)
                ->and($metadata->getType())->toBe(ArticleType::TUTORIAL)
                ->and($metadata->isFeatured())->toBeFalse()
                ->and($metadata->isPinned())->toBeFalse()
                ->and($metadata->getPublishedAt())->toBeInstanceOf(DateTime::class);
        });
    });
}

function testArticleMetadataDraftFactory(): void
{
    describe('draft factory method', function (): void {
        it('creates draft ArticleMetadata with default article type', function (): void {
            $metadata = ArticleMetadata::draft();

            expect($metadata->getStatus())->toBe(ArticleStatus::DRAFT)
                ->and($metadata->getType())->toBe(ArticleType::ARTICLE)
                ->and($metadata->isFeatured())->toBeFalse()
                ->and($metadata->isPinned())->toBeFalse()
                ->and($metadata->getPublishedAt())->toBeNull();
        });

        it('creates draft ArticleMetadata with custom type', function (): void {
            $metadata = ArticleMetadata::draft(ArticleType::WIKI);

            expect($metadata->getStatus())->toBe(ArticleStatus::DRAFT)
                ->and($metadata->getType())->toBe(ArticleType::WIKI)
                ->and($metadata->isFeatured())->toBeFalse()
                ->and($metadata->isPinned())->toBeFalse()
                ->and($metadata->getPublishedAt())->toBeNull();
        });
    });
}

function testArticleMetadataFeaturedFactory(): void
{
    describe('featured factory method', function (): void {
        it('creates featured ArticleMetadata with default article type', function (): void {
            $metadata = ArticleMetadata::featured();

            expect($metadata->getStatus())->toBe(ArticleStatus::PUBLISHED)
                ->and($metadata->getType())->toBe(ArticleType::ARTICLE)
                ->and($metadata->isFeatured())->toBeTrue()
                ->and($metadata->isPinned())->toBeFalse()
                ->and($metadata->getPublishedAt())->toBeInstanceOf(DateTime::class);
        });

        it('creates featured ArticleMetadata with custom type', function (): void {
            $metadata = ArticleMetadata::featured(ArticleType::TUTORIAL);

            expect($metadata->getStatus())->toBe(ArticleStatus::PUBLISHED)
                ->and($metadata->getType())->toBe(ArticleType::TUTORIAL)
                ->and($metadata->isFeatured())->toBeTrue()
                ->and($metadata->isPinned())->toBeFalse()
                ->and($metadata->getPublishedAt())->toBeInstanceOf(DateTime::class);
        });
    });
}

function testArticleMetadataIsPublishedMethod(): void
{
    describe('isPublished method', function (): void {
        it('returns true for published status with past date', function (): void {
            $pastDate = new DateTime('-1 day');

            $metadata = ArticleMetadata::create(
                status: ArticleStatus::PUBLISHED,
                published_at: $pastDate
            );

            expect($metadata->isPublished())->toBeTrue();
        });

        it('returns true for published status with current date', function (): void {
            $currentDate = new DateTime;

            $metadata = ArticleMetadata::create(
                status: ArticleStatus::PUBLISHED,
                published_at: $currentDate
            );

            expect($metadata->isPublished())->toBeTrue();
        });

        it('returns false for published status with future date', function (): void {
            $futureDate = new DateTime('+1 day');

            $metadata = ArticleMetadata::create(
                status: ArticleStatus::PUBLISHED,
                published_at: $futureDate
            );

            expect($metadata->isPublished())->toBeFalse();
        });

        it('returns false for published status without published_at date', function (): void {
            $metadata = ArticleMetadata::create(
                status: ArticleStatus::PUBLISHED,
                published_at: null
            );

            expect($metadata->isPublished())->toBeFalse();
        });

        it('returns false for draft status', function (): void {
            $metadata = ArticleMetadata::create(
                status: ArticleStatus::DRAFT,
                published_at: new DateTime
            );

            expect($metadata->isPublished())->toBeFalse();
        });

        it('returns false for archived status', function (): void {
            $metadata = ArticleMetadata::create(
                status: ArticleStatus::ARCHIVED,
                published_at: new DateTime
            );

            expect($metadata->isPublished())->toBeFalse();
        });
    });
}

function testArticleMetadataEqualsMethod(): void
{
    describe('equals method', function (): void {
        it('returns true for identical metadata objects', function (): void {
            $publishedAt = new DateTime(TEST_DATE);

            $metadata1 = ArticleMetadata::create(
                status: ArticleStatus::PUBLISHED,
                type: ArticleType::TUTORIAL,
                is_featured: true,
                is_pinned: false,
                published_at: $publishedAt
            );

            $metadata2 = ArticleMetadata::create(
                status: ArticleStatus::PUBLISHED,
                type: ArticleType::TUTORIAL,
                is_featured: true,
                is_pinned: false,
                published_at: $publishedAt
            );

            expect($metadata1->equals($metadata2))->toBeTrue();
        });

        it('returns false for different status', function (): void {
            $metadata1 = ArticleMetadata::create(status: ArticleStatus::PUBLISHED);
            $metadata2 = ArticleMetadata::create(status: ArticleStatus::DRAFT);

            expect($metadata1->equals($metadata2))->toBeFalse();
        });

        it('returns false for different type', function (): void {
            $metadata1 = ArticleMetadata::create(type: ArticleType::ARTICLE);
            $metadata2 = ArticleMetadata::create(type: ArticleType::TUTORIAL);

            expect($metadata1->equals($metadata2))->toBeFalse();
        });

        it('returns false for different featured flag', function (): void {
            $metadata1 = ArticleMetadata::create(is_featured: true);
            $metadata2 = ArticleMetadata::create(is_featured: false);

            expect($metadata1->equals($metadata2))->toBeFalse();
        });

        it('returns false for different pinned flag', function (): void {
            $metadata1 = ArticleMetadata::create(is_pinned: true);
            $metadata2 = ArticleMetadata::create(is_pinned: false);

            expect($metadata1->equals($metadata2))->toBeFalse();
        });

        it('returns false for different published_at dates', function (): void {
            $date1 = new DateTime(TEST_DATE);
            $date2 = new DateTime('2023-01-02');

            $metadata1 = ArticleMetadata::create(published_at: $date1);
            $metadata2 = ArticleMetadata::create(published_at: $date2);

            expect($metadata1->equals($metadata2))->toBeFalse();
        });

        it('returns true when both have null published_at', function (): void {
            $metadata1 = ArticleMetadata::create(published_at: null);
            $metadata2 = ArticleMetadata::create(published_at: null);

            expect($metadata1->equals($metadata2))->toBeTrue();
        });

        it('returns false when one has null published_at and other has date', function (): void {
            $metadata1 = ArticleMetadata::create(published_at: null);
            $metadata2 = ArticleMetadata::create(published_at: new DateTime);

            expect($metadata1->equals($metadata2))->toBeFalse();
        });
    });
}

describe('ArticleMetadata', function (): void {
    testArticleMetadataConstructor();
    testArticleMetadataCreateFactory();
    testArticleMetadataPublishedFactory();
    testArticleMetadataDraftFactory();
    testArticleMetadataFeaturedFactory();
    testArticleMetadataIsPublishedMethod();
    testArticleMetadataEqualsMethod();
});
