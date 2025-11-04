<?php

use App\Models\Article;
use App\Models\ArticleVersion;
use App\Models\User;
use Database\Factories\ArticleVersionFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

it('can create article version', function (): void {
    $user = User::factory()->create();
    $article = Article::factory()->create(['author_id' => $user->_id]);

    $version = ArticleVersion::create([
        'article_id' => $article->_id,
        'version_number' => 1,
        'title' => 'Version Title',
        'content' => 'Version Content',
        'author_id' => $user->_id,
        'status' => 'draft',
        'type' => 'article',
        'slug' => 'version-slug',
        'versioned_by' => $user->_id,
    ]);

    expect($version)->toBeInstanceOf(ArticleVersion::class)
        ->and($version->article_id)->toBe($article->_id)
        ->and($version->version_number)->toBe(1)
        ->and($version->title)->toBe('Version Title');
});

it('article relationship returns BelongsTo instance', function (): void {
    $version = new ArticleVersion;

    expect($version->article())->toBeInstanceOf(BelongsTo::class);
});

it('can retrieve article through relationship', function (): void {
    $article = Article::factory()->create();

    $version = ArticleVersion::create([
        'article_id' => $article->_id,
        'version_number' => 1,
        'title' => 'Test',
        'content' => 'Content',
        'author_id' => User::factory()->create()->_id,
        'status' => 'draft',
        'type' => 'article',
        'slug' => 'test-slug',
    ]);

    $relatedArticle = $version->article;

    expect($relatedArticle)->toBeInstanceOf(Article::class)
        ->and($relatedArticle->_id)->toBe($article->_id);
});

it('author relationship returns BelongsTo instance', function (): void {
    $version = new ArticleVersion;

    expect($version->author())->toBeInstanceOf(BelongsTo::class);
});

it('can retrieve author through relationship', function (): void {
    $authorName = 'Article Author';
    $user = User::factory()->create(['name' => $authorName]);
    $article = Article::factory()->create();

    $version = ArticleVersion::create([
        'article_id' => $article->_id,
        'version_number' => 1,
        'title' => 'Test',
        'content' => 'Content',
        'author_id' => $user->_id,
        'status' => 'draft',
        'type' => 'article',
        'slug' => 'test-slug',
    ]);

    $author = $version->author;

    expect($author)->toBeInstanceOf(User::class)
        ->and($author->_id)->toBe($user->_id)
        ->and($author->name)->toBe($authorName);
});

it('versionedBy relationship returns BelongsTo instance', function (): void {
    $version = new ArticleVersion;

    expect($version->versionedBy())->toBeInstanceOf(BelongsTo::class);
});

it('can retrieve user who created version through versionedBy relationship', function (): void {
    $authorName = 'Article Author';
    $creatorName = 'Version Creator';
    $author = User::factory()->create(['name' => $authorName]);
    $versionCreator = User::factory()->create(['name' => $creatorName]);
    $article = Article::factory()->create();

    $version = ArticleVersion::create([
        'article_id' => $article->_id,
        'version_number' => 1,
        'title' => 'Test',
        'content' => 'Content',
        'author_id' => $author->_id,
        'versioned_by' => $versionCreator->_id,
        'status' => 'draft',
        'type' => 'article',
        'slug' => 'test-slug',
    ]);

    $creator = $version->versionedBy;

    expect($creator)->toBeInstanceOf(User::class)
        ->and($creator->_id)->toBe($versionCreator->_id)
        ->and($creator->name)->toBe($creatorName);
});

it('casts arrays correctly', function (): void {
    $version = ArticleVersion::factory()->create([
        'tags' => ['PHP', 'Laravel'],
        'categories' => ['Programming', 'Tutorial'],
        'meta_data' => ['difficulty' => 'intermediate'],
        'changed_fields' => ['title', 'content'],
    ]);

    expect($version->tags)->toBeArray()
        ->and($version->categories)->toBeArray()
        ->and($version->meta_data)->toBeArray()
        ->and($version->changed_fields)->toBeArray();
});

it('casts integers correctly', function (): void {
    $version = ArticleVersion::factory()->create([
        'version_number' => 5,
        'view_count' => 100,
        'like_count' => 50,
        'comment_count' => 25,
        'reading_time' => 10,
    ]);

    expect($version->version_number)->toBeInt()
        ->and($version->view_count)->toBeInt()
        ->and($version->like_count)->toBeInt()
        ->and($version->comment_count)->toBeInt()
        ->and($version->reading_time)->toBeInt();
});

it('casts booleans correctly', function (): void {
    $version = ArticleVersion::factory()->create([
        'is_featured' => true,
        'is_pinned' => false,
    ]);

    expect($version->is_featured)->toBeBool()->toBeTrue()
        ->and($version->is_pinned)->toBeBool()->toBeFalse();
});

it('casts datetime fields correctly', function (): void {
    $version = ArticleVersion::factory()->create([
        'published_at' => now(),
    ]);

    expect($version->published_at)->toBeInstanceOf(Carbon::class)
        ->and($version->created_at)->toBeInstanceOf(Carbon::class)
        ->and($version->updated_at)->toBeInstanceOf(Carbon::class);
});

it('uses mongodb connection', function (): void {
    $version = new ArticleVersion;

    expect($version->getConnectionName())->toBe('mongodb');
});

it('uses article_versions collection', function (): void {
    $version = new ArticleVersion;

    expect($version->getTable())->toBe('article_versions');
});

it('has factory class available', function (): void {
    expect(ArticleVersion::factory())->toBeInstanceOf(ArticleVersionFactory::class);
});

it('can be created with all fillable attributes', function (): void {
    $user = User::factory()->create();
    $article = Article::factory()->create();

    $version = ArticleVersion::create([
        'article_id' => $article->_id,
        'version_number' => 1,
        'title' => 'Test Title',
        'slug' => 'test-slug',
        'content' => 'Test Content',
        'excerpt' => 'Test Excerpt',
        'author_id' => $user->_id,
        'status' => 'published',
        'type' => 'article',
        'featured_image' => 'https://example.com/image.jpg',
        'tags' => ['PHP', 'Laravel'],
        'categories' => ['Programming'],
        'meta_data' => ['key' => 'value'],
        'view_count' => 100,
        'like_count' => 50,
        'comment_count' => 25,
        'reading_time' => 5,
        'is_featured' => true,
        'is_pinned' => false,
        'published_at' => now(),
        'seo_title' => 'SEO Title',
        'seo_description' => 'SEO Description',
        'seo_keywords' => 'seo, keywords',
        'versioned_by' => $user->_id,
        'version_reason' => 'Test reason',
        'changed_fields' => ['title', 'content'],
    ]);

    expect($version)->toBeInstanceOf(ArticleVersion::class)
        ->and($version->title)->toBe('Test Title')
        ->and($version->version_number)->toBe(1)
        ->and($version->status)->toBe('published')
        ->and($version->version_reason)->toBe('Test reason');
});
