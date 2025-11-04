<?php

use App\DTOs\CreateArticleDTO;
use App\Enums\ArticleStatus;
use App\Enums\ArticleType;
use App\ValueObjects\ArticleContent;
use App\ValueObjects\ArticleMetadata;
use App\ValueObjects\ArticleSEO;
use App\ValueObjects\Content;
use App\ValueObjects\Slug;
use App\ValueObjects\Title;
use App\ValueObjects\Url;
use App\ValueObjects\UserId;

describe('CreateArticleDTO Construction', function (): void {
    it('creates dto with required properties', function (): void {
        $content = ArticleContent::create(
            title: Title::from('Test Article'),
            content: Content::from('This is test content for the article'),
            slug: Slug::from('test-article')
        );

        $authorId = UserId::from('507f1f77bcf86cd799439011');

        $metadata = ArticleMetadata::create(
            status: ArticleStatus::PUBLISHED,
            type: ArticleType::ARTICLE,
            is_featured: false,
            is_pinned: false,
            published_at: new DateTime('2024-01-01 10:00:00')
        );

        $dto = new CreateArticleDTO(
            content: $content,
            author_id: $authorId,
            metadata: $metadata
        );

        expect($dto->content)->toBe($content)
            ->and($dto->author_id)->toBe($authorId)
            ->and($dto->metadata)->toBe($metadata)
            ->and($dto->featured_image)->toBeNull()
            ->and($dto->tags)->toBe([])
            ->and($dto->categories)->toBe([])
            ->and($dto->meta_data)->toBe([])
            ->and($dto->seo)->toBeNull();
    });

    it('creates dto with all optional properties', function (): void {
        $content = ArticleContent::create(
            title: Title::from('Complete Article'),
            content: Content::from('This is complete content for the article'),
            slug: Slug::from('complete-article')
        );

        $authorId = UserId::from('507f1f77bcf86cd799439012');

        $metadata = ArticleMetadata::create(
            status: ArticleStatus::DRAFT,
            type: ArticleType::TUTORIAL,
            is_featured: true,
            is_pinned: true,
            published_at: null
        );

        $featuredImage = Url::from('https://example.com/image.jpg');
        $tags = ['php', 'laravel', 'testing'];
        $categories = ['programming', 'web-development'];
        $metaData = ['difficulty' => 'intermediate', 'duration' => '30 minutes'];

        $seo = ArticleSEO::create(
            seo_title: Title::from('SEO Title for Article'),
            seo_description: 'This is the SEO description',
            seo_keywords: 'keyword1, keyword2, keyword3'
        );

        $dto = new CreateArticleDTO(
            content: $content,
            author_id: $authorId,
            metadata: $metadata,
            featured_image: $featuredImage,
            tags: $tags,
            categories: $categories,
            meta_data: $metaData,
            seo: $seo
        );

        expect($dto->content)->toBe($content)
            ->and($dto->author_id)->toBe($authorId)
            ->and($dto->metadata)->toBe($metadata)
            ->and($dto->featured_image)->toBe($featuredImage)
            ->and($dto->tags)->toBe($tags)
            ->and($dto->categories)->toBe($categories)
            ->and($dto->meta_data)->toBe($metaData)
            ->and($dto->seo)->toBe($seo);
    });
});

describe('CreateArticleDTO toArray', function (): void {
    it('converts to array with minimal data', function (): void {
        $content = ArticleContent::create(
            title: Title::from('Array Test'),
            content: Content::from('Content for array conversion test'),
            slug: Slug::from('array-test')
        );

        $authorId = UserId::from('507f1f77bcf86cd799439013');

        $metadata = ArticleMetadata::create(
            status: ArticleStatus::PUBLISHED,
            type: ArticleType::ARTICLE,
            is_featured: false,
            is_pinned: false,
            published_at: new DateTime('2024-01-15 14:30:00')
        );

        $dto = new CreateArticleDTO(
            content: $content,
            author_id: $authorId,
            metadata: $metadata
        );

        $array = $dto->toArray();

        expect($array)->toHaveKey('title', 'Array Test')
            ->and($array)->toHaveKey('slug', 'array-test')
            ->and($array)->toHaveKey('content', 'Content for array conversion test')
            ->and($array)->toHaveKey('author_id', '507f1f77bcf86cd799439013')
            ->and($array)->toHaveKey('status', 'published')
            ->and($array)->toHaveKey('type', 'article')
            ->and($array)->toHaveKey('featured_image', null)
            ->and($array)->toHaveKey('tags', [])
            ->and($array)->toHaveKey('categories', [])
            ->and($array)->toHaveKey('meta_data', [])
            ->and($array)->toHaveKey('view_count', 0)
            ->and($array)->toHaveKey('like_count', 0)
            ->and($array)->toHaveKey('comment_count', 0)
            ->and($array)->toHaveKey('is_featured', false)
            ->and($array)->toHaveKey('is_pinned', false)
            ->and($array)->toHaveKey('seo_title', null)
            ->and($array)->toHaveKey('seo_description', null)
            ->and($array)->toHaveKey('seo_keywords', null);
    });

    it('converts to array with all data', function (): void {
        $content = ArticleContent::create(
            title: Title::from('Full Array Test'),
            content: Content::from('Complete content for full array conversion test'),
            slug: Slug::from('full-array-test')
        );

        $authorId = UserId::from('507f1f77bcf86cd799439014');

        $metadata = ArticleMetadata::create(
            status: ArticleStatus::DRAFT,
            type: ArticleType::TUTORIAL,
            is_featured: true,
            is_pinned: true,
            published_at: new DateTime('2024-02-01 09:00:00')
        );

        $featuredImage = Url::from('https://example.com/featured.jpg');
        $tags = ['test', 'array'];
        $categories = ['testing'];
        $metaData = ['level' => 'advanced'];

        $seo = ArticleSEO::create(
            seo_title: Title::from('SEO Title Test'),
            seo_description: 'SEO description for testing',
            seo_keywords: 'seo, test, keywords'
        );

        $dto = new CreateArticleDTO(
            content: $content,
            author_id: $authorId,
            metadata: $metadata,
            featured_image: $featuredImage,
            tags: $tags,
            categories: $categories,
            meta_data: $metaData,
            seo: $seo
        );

        $array = $dto->toArray();

        expect($array)->toHaveKey('title', 'Full Array Test')
            ->and($array)->toHaveKey('featured_image', 'https://example.com/featured.jpg')
            ->and($array)->toHaveKey('tags', $tags)
            ->and($array)->toHaveKey('categories', $categories)
            ->and($array)->toHaveKey('meta_data', $metaData)
            ->and($array)->toHaveKey('is_featured', true)
            ->and($array)->toHaveKey('is_pinned', true)
            ->and($array)->toHaveKey('seo_title', 'SEO Title Test')
            ->and($array)->toHaveKey('seo_description', 'SEO description for testing')
            ->and($array)->toHaveKey('seo_keywords', 'seo, test, keywords');
    });
});

describe('CreateArticleDTO fromArray', function (): void {
    it('creates from array with minimal data', function (): void {
        $data = [
            'title' => 'From Array Test',
            'content' => 'Content created from array data',
            'author_id' => '507f1f77bcf86cd799439015',
        ];

        $dto = CreateArticleDTO::fromArray($data);

        expect($dto->content->getTitle()->getValue())->toBe('From Array Test')
            ->and($dto->content->getContent()->getValue())->toBe('Content created from array data')
            ->and($dto->author_id->getValue())->toBe('507f1f77bcf86cd799439015')
            ->and($dto->metadata->getStatus())->toBe(ArticleStatus::DRAFT)
            ->and($dto->metadata->getType())->toBe(ArticleType::ARTICLE)
            ->and($dto->featured_image)->toBeNull()
            ->and($dto->tags)->toBe([])
            ->and($dto->categories)->toBe([])
            ->and($dto->meta_data)->toBe([])
            ->and($dto->seo)->toBeNull();
    });

    it('creates from array with custom slug', function (): void {
        $data = [
            'title' => 'Custom Slug Test',
            'content' => 'Content with custom slug',
            'author_id' => '507f1f77bcf86cd799439016',
            'slug' => 'custom-test-slug',
        ];

        $dto = CreateArticleDTO::fromArray($data);

        expect($dto->content->getGeneratedSlug()->getValue())->toBe('custom-test-slug');
    });

    it('creates from array with all optional data', function (): void {
        $data = [
            'title' => 'Complete From Array',
            'content' => 'Complete content from array',
            'author_id' => '507f1f77bcf86cd799439017',
            'status' => 'published',
            'type' => 'tutorial',
            'featured_image' => 'https://example.com/complete.jpg',
            'tags' => ['complete', 'array'],
            'categories' => ['testing', 'development'],
            'meta_data' => ['complexity' => 'high'],
            'is_featured' => true,
            'is_pinned' => true,
            'published_at' => '2024-03-01 12:00:00',
            'seo_title' => 'Complete SEO Title',
            'seo_description' => 'Complete SEO description',
            'seo_keywords' => 'complete, seo, keywords',
        ];

        $dto = CreateArticleDTO::fromArray($data);

        expect($dto->content->getTitle()->getValue())->toBe('Complete From Array')
            ->and($dto->metadata->getStatus())->toBe(ArticleStatus::PUBLISHED)
            ->and($dto->metadata->getType())->toBe(ArticleType::TUTORIAL)
            ->and($dto->featured_image->getValue())->toBe('https://example.com/complete.jpg')
            ->and($dto->tags)->toBe(['complete', 'array'])
            ->and($dto->categories)->toBe(['testing', 'development'])
            ->and($dto->meta_data)->toBe(['complexity' => 'high'])
            ->and($dto->metadata->isFeatured())->toBeTrue()
            ->and($dto->metadata->isPinned())->toBeTrue()
            ->and($dto->seo->getSeoTitle()->getValue())->toBe('Complete SEO Title')
            ->and($dto->seo->getSeoDescription())->toBe('Complete SEO description')
            ->and($dto->seo->getSeoKeywords())->toBe('complete, seo, keywords');
    });

    it('creates from array with partial seo data', function (): void {
        $data = [
            'title' => 'Partial SEO Test',
            'content' => 'Content with partial SEO',
            'author_id' => '507f1f77bcf86cd799439018',
            'seo_description' => 'Only SEO description provided',
        ];

        $dto = CreateArticleDTO::fromArray($data);

        expect($dto->seo)->not->toBeNull()
            ->and($dto->seo->getSeoTitle())->toBeNull()
            ->and($dto->seo->getSeoDescription())->toBe('Only SEO description provided')
            ->and($dto->seo->getSeoKeywords())->toBeNull();
    });

    it('creates from array with only seo keywords', function (): void {
        $data = [
            'title' => 'SEO Keywords Only',
            'content' => 'Content with only SEO keywords',
            'author_id' => '507f1f77bcf86cd799439019',
            'seo_keywords' => 'keyword1, keyword2',
        ];

        $dto = CreateArticleDTO::fromArray($data);

        expect($dto->seo)->not->toBeNull()
            ->and($dto->seo->getSeoTitle())->toBeNull()
            ->and($dto->seo->getSeoDescription())->toBeNull()
            ->and($dto->seo->getSeoKeywords())->toBe('keyword1, keyword2');
    });
});

describe('CreateArticleDTO Validation Errors', function (): void {
    it('throws exception when title is not a string', function (): void {
        $data = [
            'title' => 123,
            'content' => 'Valid content',
            'author_id' => '507f1f77bcf86cd799439020',
        ];

        CreateArticleDTO::fromArray($data);
    })->throws(InvalidArgumentException::class, 'Title, content and author_id must be strings');

    it('throws exception when content is not a string', function (): void {
        $data = [
            'title' => 'Valid title',
            'content' => ['invalid'],
            'author_id' => '507f1f77bcf86cd799439021',
        ];

        CreateArticleDTO::fromArray($data);
    })->throws(InvalidArgumentException::class, 'Title, content and author_id must be strings');

    it('throws exception when author_id is not a string', function (): void {
        $data = [
            'title' => 'Valid title',
            'content' => 'Valid content',
            'author_id' => 12345,
        ];

        CreateArticleDTO::fromArray($data);
    })->throws(InvalidArgumentException::class, 'Title, content and author_id must be strings');

    it('throws exception when slug is not a string', function (): void {
        $data = [
            'title' => 'Valid title',
            'content' => 'Valid content',
            'author_id' => '507f1f77bcf86cd799439022',
            'slug' => 123,
        ];

        CreateArticleDTO::fromArray($data);
    })->throws(InvalidArgumentException::class, 'Field slug must be a string or null');

    it('throws exception when seo_title is not a string', function (): void {
        $data = [
            'title' => 'Valid title',
            'content' => 'Valid content',
            'author_id' => '507f1f77bcf86cd799439023',
            'seo_title' => ['invalid'],
        ];

        CreateArticleDTO::fromArray($data);
    })->throws(InvalidArgumentException::class, 'Field seo_title must be a string or null');

    it('throws exception when seo_description is not a string', function (): void {
        $data = [
            'title' => 'Valid title',
            'content' => 'Valid content',
            'author_id' => '507f1f77bcf86cd799439024',
            'seo_description' => 123,
        ];

        CreateArticleDTO::fromArray($data);
    })->throws(InvalidArgumentException::class, 'Field seo_description must be a string or null');

    it('throws exception when seo_keywords is not a string', function (): void {
        $data = [
            'title' => 'Valid title',
            'content' => 'Valid content',
            'author_id' => '507f1f77bcf86cd799439025',
            'seo_keywords' => true,
        ];

        CreateArticleDTO::fromArray($data);
    })->throws(InvalidArgumentException::class, 'Field seo_keywords must be a string or null');

    it('throws exception when featured_image is not a string', function (): void {
        $data = [
            'title' => 'Valid title',
            'content' => 'Valid content',
            'author_id' => '507f1f77bcf86cd799439026',
            'featured_image' => 999,
        ];

        CreateArticleDTO::fromArray($data);
    })->throws(InvalidArgumentException::class, 'Field featured_image must be a string or null');

    it('throws exception when published_at is not a string', function (): void {
        $data = [
            'title' => 'Valid title',
            'content' => 'Valid content',
            'author_id' => '507f1f77bcf86cd799439027',
            'published_at' => false,
        ];

        CreateArticleDTO::fromArray($data);
    })->throws(InvalidArgumentException::class, 'Field published_at must be a string or null');

    it('throws exception when is_featured is not a boolean', function (): void {
        $data = [
            'title' => 'Valid title',
            'content' => 'Valid content',
            'author_id' => '507f1f77bcf86cd799439028',
            'is_featured' => 'yes',
        ];

        CreateArticleDTO::fromArray($data);
    })->throws(InvalidArgumentException::class, 'is_featured and is_pinned must be booleans');

    it('throws exception when is_pinned is not a boolean', function (): void {
        $data = [
            'title' => 'Valid title',
            'content' => 'Valid content',
            'author_id' => '507f1f77bcf86cd799439029',
            'is_pinned' => 1,
        ];

        CreateArticleDTO::fromArray($data);
    })->throws(InvalidArgumentException::class, 'is_featured and is_pinned must be booleans');

    it('throws exception when status is not a string or int', function (): void {
        $data = [
            'title' => 'Valid title',
            'content' => 'Valid content',
            'author_id' => '507f1f77bcf86cd799439030',
            'status' => ['invalid'],
        ];

        CreateArticleDTO::fromArray($data);
    })->throws(InvalidArgumentException::class, 'Status and type must be strings or ints');

    it('throws exception when type is not a string or int', function (): void {
        $data = [
            'title' => 'Valid title',
            'content' => 'Valid content',
            'author_id' => '507f1f77bcf86cd799439031',
            'type' => ['invalid'],
        ];

        CreateArticleDTO::fromArray($data);
    })->throws(InvalidArgumentException::class, 'Status and type must be strings or ints');
});

describe('CreateArticleDTO Array Handling', function (): void {
    it('filters non-string items from tags array', function (): void {
        $data = [
            'title' => 'Tags Filter Test',
            'content' => 'Testing tag filtering',
            'author_id' => '507f1f77bcf86cd799439032',
            'tags' => ['valid', 123, 'also-valid', null, true, 'last-valid'],
        ];

        $dto = CreateArticleDTO::fromArray($data);

        expect($dto->tags)->toBe(['valid', 'also-valid', 'last-valid']);
    });

    it('returns empty array when tags is not an array', function (): void {
        $data = [
            'title' => 'Invalid Tags Test',
            'content' => 'Testing invalid tags',
            'author_id' => '507f1f77bcf86cd799439033',
            'tags' => 'not-an-array',
        ];

        $dto = CreateArticleDTO::fromArray($data);

        expect($dto->tags)->toBe([]);
    });

    it('filters non-string items from categories array', function (): void {
        $data = [
            'title' => 'Categories Filter Test',
            'content' => 'Testing category filtering',
            'author_id' => '507f1f77bcf86cd799439034',
            'categories' => ['valid-cat', 456, 'another-cat', false],
        ];

        $dto = CreateArticleDTO::fromArray($data);

        expect($dto->categories)->toBe(['valid-cat', 'another-cat']);
    });

    it('returns empty array when categories is not an array', function (): void {
        $data = [
            'title' => 'Invalid Categories Test',
            'content' => 'Testing invalid categories',
            'author_id' => '507f1f77bcf86cd799439035',
            'categories' => 123,
        ];

        $dto = CreateArticleDTO::fromArray($data);

        expect($dto->categories)->toBe([]);
    });

    it('filters non-string keys from meta_data', function (): void {
        $data = [
            'title' => 'Meta Data Filter Test',
            'content' => 'Testing meta data filtering',
            'author_id' => '507f1f77bcf86cd799439036',
            'meta_data' => [
                'valid_key' => 'value1',
                0 => 'numeric-key-value',
                'another_key' => 123,
                1 => 'another-numeric',
            ],
        ];

        $dto = CreateArticleDTO::fromArray($data);

        expect($dto->meta_data)->toBe([
            'valid_key' => 'value1',
            'another_key' => 123,
        ]);
    });

    it('returns empty array when meta_data is not an array', function (): void {
        $data = [
            'title' => 'Invalid Meta Data Test',
            'content' => 'Testing invalid meta data',
            'author_id' => '507f1f77bcf86cd799439037',
            'meta_data' => 'not-an-array',
        ];

        $dto = CreateArticleDTO::fromArray($data);

        expect($dto->meta_data)->toBe([]);
    });

    it('creates seo object when only seo_title is provided', function (): void {
        $data = [
            'title' => 'Only SEO Title',
            'content' => 'Content with only SEO title',
            'author_id' => '507f1f77bcf86cd799439038',
            'seo_title' => 'Just the SEO Title',
        ];

        $dto = CreateArticleDTO::fromArray($data);

        expect($dto->seo)->not->toBeNull()
            ->and($dto->seo->getSeoTitle()->getValue())->toBe('Just the SEO Title')
            ->and($dto->seo->getSeoDescription())->toBeNull()
            ->and($dto->seo->getSeoKeywords())->toBeNull();
    });
});
