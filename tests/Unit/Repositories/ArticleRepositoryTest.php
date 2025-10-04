<?php

use App\DTOs\CreateArticleDTO;
use App\Enums\ArticleStatus;
use App\Enums\ArticleType;
use App\Models\Article;
use App\Models\User;
use App\Repositories\ArticleRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

function setupRepository(): ArticleRepository
{
    return new ArticleRepository(new Article);
}

function cleanupAfterTest(): void
{
    Article::query()->delete();
    User::query()->delete();
}

function testFindMethods(): void
{
    describe('findById method', function (): void {
        it('returns article when found', function (): void {
            $repository = setupRepository();
            $article = Article::factory()->create();

            $result = $repository->findById($article->_id);

            expect($result)->not->toBeNull()
                ->and($result->_id)->toBe($article->_id)
                ->and($result->title)->toBe($article->title);
        });

        it('returns null when article not found', function (): void {
            $repository = setupRepository();
            $result = $repository->findById('507f1f77bcf86cd799439011');

            expect($result)->toBeNull();
        });
    });

    describe('findBySlug method', function (): void {
        it('returns article when found by slug', function (): void {
            $repository = setupRepository();
            $article = Article::factory()->create(['slug' => 'test-article-slug']);

            $result = $repository->findBySlug('test-article-slug');

            expect($result)->not->toBeNull()
                ->and($result->slug)->toBe('test-article-slug')
                ->and($result->_id)->toBe($article->_id);
        });

        it('returns null when article not found by slug', function (): void {
            $repository = setupRepository();
            $result = $repository->findBySlug('non-existent-slug');

            expect($result)->toBeNull();
        });
    });
}

function testCreateMethod(): void
{
    describe('create method', function (): void {
        it('creates article from DTO', function (): void {
            $repository = setupRepository();
            $user = User::factory()->create();
            $dto = CreateArticleDTO::fromArray([
                'title' => 'Test Article',
                'content' => 'Test content here',
                'slug' => 'test-article',
                'status' => ArticleStatus::DRAFT->value,
                'type' => ArticleType::ARTICLE->value,
                'author_id' => $user->_id,
            ]);

            $result = $repository->create($dto);

            expect($result)->toBeInstanceOf(Article::class)
                ->and($result->title)->toBe('Test Article')
                ->and($result->content)->toBe('Test content here')
                ->and($result->slug)->toBe('test-article')
                ->and($result->status)->toBe(ArticleStatus::DRAFT->value)
                ->and($result->type)->toBe(ArticleType::ARTICLE->value)
                ->and($result->author_id)->toBe($user->_id);
        });
    });
}

function testPaginationMethods(): void
{
    describe('paginate method', function (): void {
        it('paginates articles without filters', function (): void {
            $repository = setupRepository();
            Article::factory()->count(25)->create();

            $result = $repository->paginate(10);

            expect($result)->toBeInstanceOf(LengthAwarePaginator::class)
                ->and($result->perPage())->toBe(10)
                ->and($result->total())->toBe(25)
                ->and(count($result->items()))->toBe(10);
        });

        it('paginates articles with status filter', function (): void {
            $repository = setupRepository();
            Article::factory()->count(5)->create(['status' => ArticleStatus::PUBLISHED]);
            Article::factory()->count(3)->create(['status' => ArticleStatus::DRAFT]);

            $result = $repository->paginate(10, ['status' => ArticleStatus::PUBLISHED->value]);

            expect($result->total())->toBe(5);
            foreach ($result->items() as $article) {
                expect($article->status)->toBe(ArticleStatus::PUBLISHED->value);
            }
        });

        it('paginates articles with type filter', function (): void {
            $repository = setupRepository();
            Article::factory()->count(4)->create(['type' => ArticleType::TUTORIAL]);
            Article::factory()->count(6)->create(['type' => ArticleType::ARTICLE]);

            $result = $repository->paginate(10, ['type' => ArticleType::TUTORIAL->value]);

            expect($result->total())->toBe(4);
            foreach ($result->items() as $article) {
                expect($article->type)->toBe(ArticleType::TUTORIAL->value);
            }
        });

        it('paginates articles with author_id filter', function (): void {
            $repository = setupRepository();
            $user1 = User::factory()->create();
            $user2 = User::factory()->create();

            Article::factory()->count(3)->create(['author_id' => $user1->_id]);
            Article::factory()->count(2)->create(['author_id' => $user2->_id]);

            $result = $repository->paginate(10, ['author_id' => $user1->_id]);

            expect($result->total())->toBe(3);
            foreach ($result->items() as $article) {
                expect($article->author_id)->toBe($user1->_id);
            }
        });

        it('paginates articles with featured filter', function (): void {
            $repository = setupRepository();
            Article::factory()->count(3)->create(['is_featured' => true]);
            Article::factory()->count(7)->create(['is_featured' => false]);

            $result = $repository->paginate(10, ['featured' => true]);

            expect($result->total())->toBe(3);
            foreach ($result->items() as $article) {
                expect($article->is_featured)->toBeTrue();
            }
        });

        it('paginates articles with multiple filters', function (): void {
            $repository = setupRepository();
            $user = User::factory()->create();

            Article::factory()->count(2)->create([
                'status' => ArticleStatus::PUBLISHED,
                'type' => ArticleType::ARTICLE,
                'author_id' => $user->_id,
                'is_featured' => true,
            ]);

            Article::factory()->count(3)->create([
                'status' => ArticleStatus::DRAFT,
                'type' => ArticleType::ARTICLE,
                'author_id' => $user->_id,
                'is_featured' => true,
            ]);

            $filters = [
                'status' => ArticleStatus::PUBLISHED->value,
                'type' => ArticleType::ARTICLE->value,
                'author_id' => $user->_id,
                'featured' => true,
            ];

            $result = $repository->paginate(10, $filters);

            expect($result->total())->toBe(2);
            foreach ($result->items() as $article) {
                expect($article->status)->toBe(ArticleStatus::PUBLISHED->value)
                    ->and($article->type)->toBe(ArticleType::ARTICLE->value)
                    ->and($article->author_id)->toBe($user->_id)
                    ->and($article->is_featured)->toBeTrue();
            }
        });
    });
}

function testQueryMethods(): void
{
    describe('getPublished method', function (): void {
        it('returns only published articles with published_at in past', function (): void {
            $repository = setupRepository();
            Article::factory()->count(3)->create([
                'status' => 'published',
                'published_at' => now()->subDay(),
            ]);

            Article::factory()->count(2)->create([
                'status' => 'draft',
                'published_at' => now()->subDay(),
            ]);

            Article::factory()->count(1)->create([
                'status' => 'published',
                'published_at' => now()->addDay(),
            ]);

            $result = $repository->getPublished();

            expect($result)->toBeInstanceOf(Collection::class)
                ->and($result->count())->toBe(3);

            foreach ($result as $article) {
                expect($article->status)->toBe('published')
                    ->and($article->published_at->isPast())->toBeTrue();
            }
        });
    });

    describe('getFeatured method', function (): void {
        it('returns only featured published articles', function (): void {
            $repository = setupRepository();
            Article::factory()->count(2)->create([
                'is_featured' => true,
                'status' => 'published',
            ]);

            Article::factory()->count(3)->create([
                'is_featured' => false,
                'status' => 'published',
            ]);

            Article::factory()->count(1)->create([
                'is_featured' => true,
                'status' => 'draft',
            ]);

            $result = $repository->getFeatured();

            expect($result)->toBeInstanceOf(Collection::class)
                ->and($result->count())->toBe(2);

            foreach ($result as $article) {
                expect($article->is_featured)->toBeTrue()
                    ->and($article->status)->toBe('published');
            }
        });
    });

    describe('getByAuthor method', function (): void {
        it('returns articles by specific author', function (): void {
            $repository = setupRepository();
            $user1 = User::factory()->create();
            $user2 = User::factory()->create();

            Article::factory()->count(4)->create(['author_id' => $user1->_id]);
            Article::factory()->count(2)->create(['author_id' => $user2->_id]);

            $result = $repository->getByAuthor($user1->_id);

            expect($result)->toBeInstanceOf(Collection::class)
                ->and($result->count())->toBe(4);

            foreach ($result as $article) {
                expect($article->author_id)->toBe($user1->_id);
            }
        });
    });

    describe('getByType method', function (): void {
        it('returns only published articles of specific type', function (): void {
            $repository = setupRepository();
            Article::factory()->count(3)->create([
                'type' => ArticleType::TUTORIAL,
                'status' => 'published',
            ]);

            Article::factory()->count(2)->create([
                'type' => ArticleType::ARTICLE,
                'status' => 'published',
            ]);

            Article::factory()->count(1)->create([
                'type' => ArticleType::TUTORIAL,
                'status' => 'draft',
            ]);

            $result = $repository->getByType(ArticleType::TUTORIAL->value);

            expect($result)->toBeInstanceOf(Collection::class)
                ->and($result->count())->toBe(3);

            foreach ($result as $article) {
                expect($article->type)->toBe(ArticleType::TUTORIAL->value)
                    ->and($article->status)->toBe('published');
            }
        });
    });
}

function testSearchMethods(): void
{
    describe('search method', function (): void {
        it('searches articles by term in title, content and excerpt', function (): void {
            $repository = setupRepository();
            Article::factory()->create([
                'title' => 'PHP Tutorial',
                'content' => 'Learning PHP basics',
                'excerpt' => 'Basic PHP guide',
                'status' => 'published',
            ]);

            Article::factory()->create([
                'title' => 'JavaScript Guide',
                'content' => 'Advanced PHP techniques',
                'excerpt' => 'JS fundamentals',
                'status' => 'published',
            ]);

            Article::factory()->create([
                'title' => 'Laravel Tips',
                'content' => 'Laravel best practices',
                'excerpt' => 'Great PHP framework tips',
                'status' => 'published',
            ]);

            Article::factory()->create([
                'title' => 'PHP Advanced',
                'content' => 'Advanced concepts',
                'excerpt' => 'Advanced guide',
                'status' => 'draft',
            ]);

            $result = $repository->search('PHP');

            expect($result)->toBeInstanceOf(Collection::class)
                ->and($result->count())->toBe(3);
        });
    });

    describe('getByTags method', function (): void {
        it('returns articles by single tag', function (): void {
            $repository = setupRepository();
            Article::factory()->create([
                'tags' => ['php', 'web'],
                'status' => 'published',
            ]);

            Article::factory()->create([
                'tags' => ['javascript', 'frontend'],
                'status' => 'published',
            ]);

            Article::factory()->create([
                'tags' => ['php', 'laravel'],
                'status' => 'published',
            ]);

            Article::factory()->create([
                'tags' => ['php', 'backend'],
                'status' => 'draft',
            ]);

            $result = $repository->getByTags(['php']);

            expect($result)->toBeInstanceOf(Collection::class)
                ->and($result->count())->toBe(2);
        });

        it('returns articles by multiple tags', function (): void {
            $repository = setupRepository();
            Article::factory()->create([
                'tags' => ['php', 'web'],
                'status' => 'published',
            ]);

            Article::factory()->create([
                'tags' => ['laravel', 'framework'],
                'status' => 'published',
            ]);

            Article::factory()->create([
                'tags' => ['testing', 'phpunit'],
                'status' => 'published',
            ]);

            $result = $repository->getByTags(['php', 'laravel']);

            expect($result)->toBeInstanceOf(Collection::class)
                ->and($result->count())->toBe(3);
        });

        it('returns published articles only when filtering by tags with empty array', function (): void {
            $repository = setupRepository();
            Article::factory()->count(5)->create(['status' => 'published']);
            Article::factory()->count(3)->create(['status' => 'draft']);

            $result = $repository->getByTags([]);

            expect($result)->toBeInstanceOf(Collection::class)
                ->and($result->count())->toBe(5);

            foreach ($result as $article) {
                expect($article->status)->toBe('published');
            }
        });
    });
}

function testConstructor(): void
{
    describe('constructor', function (): void {
        it('creates repository with Article model dependency', function (): void {
            $model = new Article;
            $repository = new ArticleRepository($model);

            expect($repository)->toBeInstanceOf(ArticleRepository::class);
        });
    });
}

describe('ArticleRepository', function (): void {
    afterEach(function (): void {
        cleanupAfterTest();
    });

    testFindMethods();
    testCreateMethod();
    testPaginationMethods();
    testQueryMethods();
    testSearchMethods();
    testConstructor();
});
