<?php

use App\DTOs\CreateArticleDTO;
use App\Enums\ArticleStatus;
use App\Enums\ArticleType;
use App\Models\Article;
use App\Models\User;
use App\Repositories\ArticleRepository;
use App\Services\ArticleService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

function setupArticleService(): ArticleService
{
    $repository = new ArticleRepository(new Article);

    return new ArticleService($repository);
}

function cleanupAfterArticleTest(): void
{
    Article::query()->delete();
    User::query()->delete();
}

function testArticleCreationMethods(): void
{
    describe('createArticle method', function (): void {
        it('creates article with minimal data', function (): void {
            $service = setupArticleService();
            $user = User::factory()->create();

            $dto = CreateArticleDTO::fromArray([
                'title' => 'Test Article',
                'content' => 'This is test content for the article.',
                'author_id' => $user->_id,
                'status' => ArticleStatus::DRAFT->value,
                'type' => ArticleType::ARTICLE->value,
            ]);

            $result = $service->createArticle($dto);

            expect($result)->toBeInstanceOf(Article::class)
                ->and($result->title)->toBe('Test Article')
                ->and($result->content)->toBe('This is test content for the article.')
                ->and($result->author_id)->toBe($user->_id)
                ->and($result->status)->toBe(ArticleStatus::DRAFT->value)
                ->and($result->type)->toBe(ArticleType::ARTICLE->value);
        });

        it('creates article with all data including SEO', function (): void {
            $service = setupArticleService();
            $user = User::factory()->create();

            $dto = CreateArticleDTO::fromArray([
                'title' => 'Complete Article',
                'content' => 'This is a complete article with all fields.',
                'slug' => 'complete-article',
                'excerpt' => 'Custom excerpt',
                'author_id' => $user->_id,
                'status' => ArticleStatus::PUBLISHED->value,
                'type' => ArticleType::TUTORIAL->value,
                'is_featured' => true,
                'is_pinned' => false,
                'featured_image' => 'https://example.com/image.jpg',
                'tags' => ['php', 'laravel'],
                'categories' => ['web-development'],
                'meta_data' => ['key' => 'value'],
                'seo_title' => 'SEO Title',
                'seo_description' => 'SEO Description',
                'seo_keywords' => 'seo,keywords',
            ]);

            $result = $service->createArticle($dto);

            expect($result)->toBeInstanceOf(Article::class)
                ->and($result->title)->toBe('Complete Article')
                ->and($result->slug)->toBe('complete-article')
                ->and($result->excerpt)->not->toBeEmpty()
                ->and($result->status)->toBe(ArticleStatus::PUBLISHED->value)
                ->and($result->type)->toBe(ArticleType::TUTORIAL->value)
                ->and($result->is_featured)->toBeTrue()
                ->and($result->tags)->toContain('php')
                ->and($result->tags)->toContain('laravel');
        });

        it('creates article with auto-generated slug when not provided', function (): void {
            $service = setupArticleService();
            $user = User::factory()->create();

            $dto = CreateArticleDTO::fromArray([
                'title' => 'Article Without Slug',
                'content' => 'Content for article without slug.',
                'author_id' => $user->_id,
                'status' => ArticleStatus::DRAFT->value,
                'type' => ArticleType::ARTICLE->value,
            ]);

            $result = $service->createArticle($dto);

            expect($result)->toBeInstanceOf(Article::class)
                ->and($result->title)->toBe('Article Without Slug')
                ->and($result->slug)->not->toBeEmpty();
        });

        it('creates article with auto-generated excerpt when not provided', function (): void {
            $service = setupArticleService();
            $user = User::factory()->create();
            $longContent = str_repeat('This is a long content. ', 50);

            $dto = CreateArticleDTO::fromArray([
                'title' => 'Article With Long Content',
                'content' => $longContent,
                'author_id' => $user->_id,
                'status' => ArticleStatus::DRAFT->value,
                'type' => ArticleType::ARTICLE->value,
            ]);

            $result = $service->createArticle($dto);

            expect($result)->toBeInstanceOf(Article::class)
                ->and($result->excerpt)->not->toBeEmpty()
                ->and($result->excerpt)->toEndWith('...');
        });

        it('creates article with reading time calculation', function (): void {
            $service = setupArticleService();
            $user = User::factory()->create();
            $content = str_repeat('word ', 300);

            $dto = CreateArticleDTO::fromArray([
                'title' => 'Article With Reading Time',
                'content' => $content,
                'author_id' => $user->_id,
                'status' => ArticleStatus::DRAFT->value,
                'type' => ArticleType::ARTICLE->value,
            ]);

            $result = $service->createArticle($dto);

            expect($result)->toBeInstanceOf(Article::class)
                ->and($result->reading_time)->toBeGreaterThan(0);
        });

        it('creates article with SEO data when provided', function (): void {
            $service = setupArticleService();
            $user = User::factory()->create();

            $dto = CreateArticleDTO::fromArray([
                'title' => 'SEO Article',
                'content' => 'Article with SEO optimization.',
                'author_id' => $user->_id,
                'status' => ArticleStatus::PUBLISHED->value,
                'type' => ArticleType::ARTICLE->value,
                'seo_title' => 'Custom SEO Title',
                'seo_description' => 'Custom SEO Description',
                'seo_keywords' => 'seo,optimization',
            ]);

            $result = $service->createArticle($dto);

            expect($result)->toBeInstanceOf(Article::class)
                ->and($result->title)->toBe('SEO Article');
        });

        it('creates article with partial SEO data', function (): void {
            $service = setupArticleService();
            $user = User::factory()->create();

            $dto = CreateArticleDTO::fromArray([
                'title' => 'Partial SEO Article',
                'content' => 'Article with partial SEO data.',
                'author_id' => $user->_id,
                'status' => ArticleStatus::PUBLISHED->value,
                'type' => ArticleType::ARTICLE->value,
                'seo_title' => 'Only SEO Title',
            ]);

            $result = $service->createArticle($dto);

            expect($result)->toBeInstanceOf(Article::class)
                ->and($result->title)->toBe('Partial SEO Article');
        });
    });
}

function testArticleFindMethods(): void
{
    describe('getArticleById method', function (): void {
        it('returns article when found', function (): void {
            $service = setupArticleService();
            $article = Article::factory()->create();

            $result = $service->getArticleById($article->_id);

            expect($result)->toBeInstanceOf(Article::class)
                ->and($result->_id)->toBe($article->_id);
        });

        it('returns null when article not found', function (): void {
            $service = setupArticleService();

            $result = $service->getArticleById('507f1f77bcf86cd799439011');

            expect($result)->toBeNull();
        });
    });

    describe('getArticleBySlug method', function (): void {
        it('returns article when found by slug', function (): void {
            $service = setupArticleService();
            Article::factory()->create(['slug' => 'test-article']);

            $result = $service->getArticleBySlug('test-article');

            expect($result)->toBeInstanceOf(Article::class)
                ->and($result->slug)->toBe('test-article');
        });

        it('returns null when article not found by slug', function (): void {
            $service = setupArticleService();

            $result = $service->getArticleBySlug('nonexistent-slug');

            expect($result)->toBeNull();
        });
    });
}

function testArticlePaginationMethods(): void
{
    describe('getArticles method', function (): void {
        it('returns paginated articles with default parameters', function (): void {
            $service = setupArticleService();
            Article::factory()->count(20)->create();

            $result = $service->getArticles();

            expect($result)->toBeInstanceOf(LengthAwarePaginator::class)
                ->and($result->perPage())->toBe(15)
                ->and($result->total())->toBe(20);
        });

        it('returns paginated articles with custom parameters', function (): void {
            $service = setupArticleService();
            Article::factory()->count(25)->create(['status' => ArticleStatus::PUBLISHED]);

            $result = $service->getArticles(10, ['status' => ArticleStatus::PUBLISHED->value]);

            expect($result)->toBeInstanceOf(LengthAwarePaginator::class)
                ->and($result->perPage())->toBe(10)
                ->and($result->total())->toBe(25);
        });
    });
}

function testArticleQueryMethods(): void
{
    describe('getPublishedArticles method', function (): void {
        it('returns collection of published articles', function (): void {
            $service = setupArticleService();
            Article::factory()->count(3)->create([
                'status' => 'published',
                'published_at' => now()->subDay(),
            ]);
            Article::factory()->count(2)->create(['status' => 'draft']);

            $result = $service->getPublishedArticles();

            expect($result)->toBeInstanceOf(Collection::class)
                ->and($result->count())->toBe(3);
        });
    });

    describe('getFeaturedArticles method', function (): void {
        it('returns collection of featured articles', function (): void {
            $service = setupArticleService();
            Article::factory()->count(2)->create([
                'is_featured' => true,
                'status' => 'published',
            ]);
            Article::factory()->count(3)->create(['is_featured' => false]);

            $result = $service->getFeaturedArticles();

            expect($result)->toBeInstanceOf(Collection::class)
                ->and($result->count())->toBe(2);
        });
    });

    describe('getArticlesByAuthor method', function (): void {
        it('returns collection of articles by author', function (): void {
            $service = setupArticleService();
            $user = User::factory()->create();
            Article::factory()->count(3)->create(['author_id' => $user->_id]);
            Article::factory()->count(2)->create();

            $result = $service->getArticlesByAuthor($user->_id);

            expect($result)->toBeInstanceOf(Collection::class)
                ->and($result->count())->toBe(3);
        });
    });

    describe('getArticlesByType method', function (): void {
        it('returns collection of articles by type', function (): void {
            $service = setupArticleService();
            Article::factory()->count(2)->create([
                'type' => ArticleType::TUTORIAL,
                'status' => 'published',
            ]);
            Article::factory()->count(3)->create([
                'type' => ArticleType::ARTICLE,
                'status' => 'published',
            ]);

            $result = $service->getArticlesByType(ArticleType::TUTORIAL);

            expect($result)->toBeInstanceOf(Collection::class)
                ->and($result->count())->toBe(2);
        });
    });
}

function testArticleSearchMethods(): void
{
    describe('searchArticles method', function (): void {
        it('returns collection of articles matching search term', function (): void {
            $service = setupArticleService();
            Article::factory()->create([
                'title' => 'PHP Tutorial',
                'status' => 'published',
            ]);
            Article::factory()->create([
                'title' => 'JavaScript Guide',
                'status' => 'published',
            ]);

            $result = $service->searchArticles('PHP');

            expect($result)->toBeInstanceOf(Collection::class)
                ->and($result->count())->toBe(1);
        });
    });

    describe('getArticlesByTags method', function (): void {
        it('returns collection of articles by tags', function (): void {
            $service = setupArticleService();
            Article::factory()->create([
                'tags' => ['php', 'laravel'],
                'status' => 'published',
            ]);
            Article::factory()->create([
                'tags' => ['javascript'],
                'status' => 'published',
            ]);

            $result = $service->getArticlesByTags(['php']);

            expect($result)->toBeInstanceOf(Collection::class)
                ->and($result->count())->toBe(1);
        });
    });
}

function testArticleServiceConstructor(): void
{
    describe('constructor', function (): void {
        it('creates service with repository dependency', function (): void {
            $repository = new ArticleRepository(new Article);
            $service = new ArticleService($repository);

            expect($service)->toBeInstanceOf(ArticleService::class);
        });

        it('generates slug and excerpt when DTO returns empty values', function (): void {
            $service = setupArticleService();
            $user = User::factory()->create();

            $dto = new class($user->_id) extends CreateArticleDTO
            {
                public function __construct(private readonly string $userId) {}

                public function toArray(): array
                {
                    return [
                        'title' => 'Test Article With Empty Fields',
                        'content' => 'Short content.',
                        'author_id' => $this->userId,
                        'status' => ArticleStatus::DRAFT->value,
                        'type' => ArticleType::ARTICLE->value,
                        'slug' => '',
                        'excerpt' => '',
                        'featured_image' => null,
                        'tags' => [],
                        'categories' => [],
                        'meta_data' => [],
                        'view_count' => 0,
                        'like_count' => 0,
                        'comment_count' => 0,
                        'reading_time' => 1,
                        'is_featured' => false,
                        'is_pinned' => false,
                        'published_at' => null,
                        'seo_title' => null,
                        'seo_description' => null,
                        'seo_keywords' => null,
                    ];
                }
            };

            $result = $service->createArticle($dto);

            expect($result)->toBeInstanceOf(Article::class)
                ->and($result->slug)->not->toBeEmpty()
                ->and($result->excerpt)->not->toBeEmpty();
        });

        it('generates excerpt for short content without ellipsis', function (): void {
            $service = setupArticleService();
            $user = User::factory()->create();

            $dto = new class($user->_id) extends CreateArticleDTO
            {
                public function __construct(private readonly string $userId) {}

                public function toArray(): array
                {
                    return [
                        'title' => 'Short Content Test',
                        'content' => 'Short',
                        'author_id' => $this->userId,
                        'status' => ArticleStatus::DRAFT->value,
                        'type' => ArticleType::ARTICLE->value,
                        'slug' => '',
                        'excerpt' => '',
                        'featured_image' => null,
                        'tags' => [],
                        'categories' => [],
                        'meta_data' => [],
                        'view_count' => 0,
                        'like_count' => 0,
                        'comment_count' => 0,
                        'reading_time' => 1,
                        'is_featured' => false,
                        'is_pinned' => false,
                        'published_at' => null,
                        'seo_title' => null,
                        'seo_description' => null,
                        'seo_keywords' => null,
                    ];
                }
            };

            $result = $service->createArticle($dto);

            expect($result)->toBeInstanceOf(Article::class)
                ->and($result->excerpt)->toBe('Short')
                ->and($result->excerpt)->not->toContain('...');
        });

        it('generates excerpt when not provided in DTO', function (): void {
            $service = setupArticleService();
            $user = User::factory()->create();

            $dto = CreateArticleDTO::fromArray([
                'title' => 'Article Without Excerpt',
                'content' => 'This is test content for the article that will be used to generate an excerpt.',
                'author_id' => $user->_id,
                'status' => ArticleStatus::DRAFT->value,
                'type' => ArticleType::ARTICLE->value,
            ]);

            $result = $service->createArticle($dto);

            expect($result)->toBeInstanceOf(Article::class)
                ->and($result->excerpt)->not->toBeEmpty()
                ->and($result->excerpt)->toBeString();
        });

        it('generates excerpt without ellipsis for short content', function (): void {
            $service = setupArticleService();
            $user = User::factory()->create();
            $shortContent = 'Short content';

            $dto = CreateArticleDTO::fromArray([
                'title' => 'Article With Short Content',
                'content' => $shortContent,
                'author_id' => $user->_id,
                'status' => ArticleStatus::DRAFT->value,
                'type' => ArticleType::ARTICLE->value,
            ]);

            $result = $service->createArticle($dto);

            expect($result)->toBeInstanceOf(Article::class)
                ->and($result->excerpt)->toBe($shortContent)
                ->and($result->excerpt)->not->toContain('...');
        });
    });
}

describe('ArticleService', function (): void {
    afterEach(function (): void {
        cleanupAfterArticleTest();
    });

    testArticleCreationMethods();
    testArticleFindMethods();
    testArticlePaginationMethods();
    testArticleQueryMethods();
    testArticleSearchMethods();
    testArticleServiceConstructor();
});
