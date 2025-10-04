<?php

use App\Enums\ArticleStatus;
use App\Enums\ArticleType;
use App\Models\Article;
use App\Models\User;
use Carbon\Carbon;
use Database\Factories\ArticleFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

const TEST_ARTICLE_TITLE = 'Test Article Title';
const TEST_ARTICLE_SLUG = 'test-article-slug';
const TEST_ARTICLE_CONTENT = 'This is the article content for testing purposes.';
const TEST_ARTICLE_EXCERPT = 'This is a test excerpt';
const TEST_SEO_TITLE = 'SEO Optimized Title';
const TEST_SEO_DESCRIPTION = 'SEO meta description for testing';
const TEST_SEO_KEYWORDS = 'test,seo,article';
const TEST_FEATURED_IMAGE = 'https://example.com/image.jpg';

describe('Article Model Basic Functionality', function (): void {
    it('can instantiate article model', function (): void {
        $article = new Article;

        expect($article)->toBeInstanceOf(Article::class);
    });

    it('can set and get attributes', function (): void {
        $article = new Article;
        $article->title = TEST_ARTICLE_TITLE;
        $article->slug = TEST_ARTICLE_SLUG;
        $article->content = TEST_ARTICLE_CONTENT;

        expect($article->title)->toBe(TEST_ARTICLE_TITLE)
            ->and($article->slug)->toBe(TEST_ARTICLE_SLUG)
            ->and($article->content)->toBe(TEST_ARTICLE_CONTENT);
    });
});

describe('Article Model Mass Assignment', function (): void {
    it('allows mass assignment of fillable attributes', function (): void {
        $attributes = [
            'title' => TEST_ARTICLE_TITLE,
            'slug' => TEST_ARTICLE_SLUG,
            'content' => TEST_ARTICLE_CONTENT,
            'excerpt' => TEST_ARTICLE_EXCERPT,
            'status' => ArticleStatus::DRAFT->value,
            'type' => ArticleType::POST->value,
            'featured_image' => TEST_FEATURED_IMAGE,
            'tags' => ['php', 'laravel', 'testing'],
            'categories' => ['development', 'programming'],
            'meta_data' => ['custom_field' => 'custom_value'],
            'view_count' => 100,
            'like_count' => 25,
            'comment_count' => 5,
            'reading_time' => 8,
            'is_featured' => true,
            'is_pinned' => false,
            'seo_title' => TEST_SEO_TITLE,
            'seo_description' => TEST_SEO_DESCRIPTION,
            'seo_keywords' => TEST_SEO_KEYWORDS,
        ];

        $article = new Article($attributes);

        expect($article->title)->toBe(TEST_ARTICLE_TITLE)
            ->and($article->slug)->toBe(TEST_ARTICLE_SLUG)
            ->and($article->content)->toBe(TEST_ARTICLE_CONTENT)
            ->and($article->excerpt)->toBe(TEST_ARTICLE_EXCERPT)
            ->and($article->status)->toBe(ArticleStatus::DRAFT->value)
            ->and($article->type)->toBe(ArticleType::POST->value)
            ->and($article->featured_image)->toBe(TEST_FEATURED_IMAGE)
            ->and($article->tags)->toBe(['php', 'laravel', 'testing'])
            ->and($article->categories)->toBe(['development', 'programming'])
            ->and($article->meta_data)->toBe(['custom_field' => 'custom_value'])
            ->and($article->view_count)->toBe(100)
            ->and($article->like_count)->toBe(25)
            ->and($article->comment_count)->toBe(5)
            ->and($article->reading_time)->toBe(8)
            ->and($article->is_featured)->toBe(true)
            ->and($article->is_pinned)->toBe(false)
            ->and($article->seo_title)->toBe(TEST_SEO_TITLE)
            ->and($article->seo_description)->toBe(TEST_SEO_DESCRIPTION)
            ->and($article->seo_keywords)->toBe(TEST_SEO_KEYWORDS);
    });
});

describe('Article Model Attributes Casting', function (): void {
    it('casts tags to array', function (): void {
        $article = new Article([
            'tags' => ['php', 'laravel', 'mongodb'],
        ]);

        expect($article->tags)->toBeArray()
            ->and($article->tags)->toBe(['php', 'laravel', 'mongodb']);
    });

    it('casts categories to array', function (): void {
        $article = new Article([
            'categories' => ['programming', 'web-development'],
        ]);

        expect($article->categories)->toBeArray()
            ->and($article->categories)->toBe(['programming', 'web-development']);
    });

    it('casts meta_data to array', function (): void {
        $metaData = ['featured' => true, 'priority' => 'high'];
        $article = new Article([
            'meta_data' => $metaData,
        ]);

        expect($article->meta_data)->toBeArray()
            ->and($article->meta_data)->toBe($metaData);
    });

    it('casts numeric fields to integers', function (): void {
        $article = new Article([
            'view_count' => '150',
            'like_count' => '30',
            'comment_count' => '8',
            'reading_time' => '12',
        ]);

        expect($article->view_count)->toBeInt()
            ->and($article->view_count)->toBe(150)
            ->and($article->like_count)->toBeInt()
            ->and($article->like_count)->toBe(30)
            ->and($article->comment_count)->toBeInt()
            ->and($article->comment_count)->toBe(8)
            ->and($article->reading_time)->toBeInt()
            ->and($article->reading_time)->toBe(12);
    });

    it('casts boolean fields to booleans', function (): void {
        $article = new Article([
            'is_featured' => '1',
            'is_pinned' => '0',
        ]);

        expect($article->is_featured)->toBeBool()
            ->and($article->is_featured)->toBe(true)
            ->and($article->is_pinned)->toBeBool()
            ->and($article->is_pinned)->toBe(false);
    });

    it('casts datetime fields to Carbon instances', function (): void {
        $publishedAt = now();
        $article = new Article([
            'published_at' => $publishedAt,
        ]);

        expect($article->published_at)->toBeInstanceOf(Carbon::class);
    });
});

describe('Article Model Hidden Attributes', function (): void {
    it('hides deleted_at from array serialization', function (): void {
        $article = new Article;
        $articleArray = $article->toArray();

        expect($articleArray)->not->toHaveKey('deleted_at');
    });
});

describe('Article Model Database Operations', function (): void {
    it('uses mongodb connection', function (): void {
        $article = new Article;

        expect($article->getConnectionName())->toBe('mongodb');
    });

    it('uses articles collection', function (): void {
        $article = new Article;

        expect($article->getTable())->toBe('articles');
    });
});

describe('Article Model Configuration', function (): void {
    it('has correct fillable attributes', function (): void {
        $article = new Article;
        $expectedFillable = [
            'title',
            'slug',
            'content',
            'excerpt',
            'author_id',
            'status',
            'type',
            'featured_image',
            'tags',
            'categories',
            'meta_data',
            'view_count',
            'like_count',
            'comment_count',
            'reading_time',
            'is_featured',
            'is_pinned',
            'published_at',
            'seo_title',
            'seo_description',
            'seo_keywords',
        ];

        expect($article->getFillable())->toBe($expectedFillable);
    });

    it('has correct hidden attributes', function (): void {
        $article = new Article;
        $expectedHidden = ['deleted_at'];

        expect($article->getHidden())->toBe($expectedHidden);
    });

    it('has correct cast configuration', function (): void {
        $article = new Article;
        $expectedCasts = [
            'tags' => 'array',
            'categories' => 'array',
            'meta_data' => 'array',
            'view_count' => 'integer',
            'like_count' => 'integer',
            'comment_count' => 'integer',
            'reading_time' => 'integer',
            'is_featured' => 'boolean',
            'is_pinned' => 'boolean',
            'published_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];

        expect($article->getCasts())->toBe($expectedCasts);
    });
});

describe('Article Model Relationships', function (): void {
    it('author relationship returns BelongsTo instance', function (): void {
        $article = new Article;
        $relation = $article->author();

        expect($relation)->toBeInstanceOf(BelongsTo::class)
            ->and($relation->getRelated())->toBeInstanceOf(User::class)
            ->and($relation->getForeignKeyName())->toBe('author_id');
    });
});

describe('Article Model Factory Integration', function (): void {
    it('has factory class available', function (): void {
        $factory = Article::factory();

        expect($factory)->toBeInstanceOf(ArticleFactory::class);
    });

    it('factory can create model instance without persisting', function (): void {
        $article = Article::factory()->make();

        expect($article)->toBeInstanceOf(Article::class)
            ->and($article->exists)->toBeFalse();
    });
});
