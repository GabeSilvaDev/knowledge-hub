<?php

use App\DTOs\CreateArticleDTO;
use App\Enums\ArticleStatus;
use App\Enums\ArticleType;
use App\Models\Article;
use App\Models\User;
use App\Repositories\ArticleRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

describe('ArticleRepository', function () {
    beforeEach(function () {
        $this->repository = new ArticleRepository(new Article);
    });

    afterEach(function () {
        Article::query()->delete();
        User::query()->delete();
    });

    describe('constructor', function () {
        it('creates repository with Article model dependency', function () {
            expect($this->repository)->toBeInstanceOf(ArticleRepository::class);
        });
    });

    describe('findById method', function () {
        it('returns article when found', function () {
            $article = Article::factory()->create();

            $result = $this->repository->findById($article->_id);

            expect($result)->not->toBeNull()
                ->and($result->_id)->toBe($article->_id)
                ->and($result->title)->toBe($article->title);
        });

        it('returns null when article not found', function () {
            $result = $this->repository->findById('507f1f77bcf86cd799439011');

            expect($result)->toBeNull();
        });
    });

    describe('findBySlug method', function () {
        it('returns article when found by slug', function () {
            $article = Article::factory()->create(['slug' => 'test-article-slug']);

            $result = $this->repository->findBySlug('test-article-slug');

            expect($result)->not->toBeNull()
                ->and($result->slug)->toBe('test-article-slug')
                ->and($result->_id)->toBe($article->_id);
        });

        it('returns null when article not found by slug', function () {
            $result = $this->repository->findBySlug('non-existent-slug');

            expect($result)->toBeNull();
        });
    });

    describe('create method', function () {
        it('creates article from DTO', function () {
            $user = User::factory()->create();
            $dto = CreateArticleDTO::fromArray([
                'title' => 'Test Article',
                'content' => 'Test content here',
                'slug' => 'test-article',
                'status' => ArticleStatus::DRAFT->value,
                'type' => ArticleType::ARTICLE->value,
                'author_id' => $user->_id,
            ]);

            $result = $this->repository->create($dto);

            expect($result)->toBeInstanceOf(Article::class)
                ->and($result->title)->toBe('Test Article')
                ->and($result->content)->toBe('Test content here')
                ->and($result->slug)->toBe('test-article')
                ->and($result->status)->toBe(ArticleStatus::DRAFT->value)
                ->and($result->type)->toBe(ArticleType::ARTICLE->value)
                ->and($result->author_id)->toBe($user->_id);
        });
    });

    describe('paginate method', function () {
        it('paginates articles without filters', function () {
            Article::factory()->count(25)->create();

            $result = $this->repository->paginate(10);

            expect($result)->toBeInstanceOf(LengthAwarePaginator::class)
                ->and($result->perPage())->toBe(10)
                ->and($result->total())->toBe(25)
                ->and(count($result->items()))->toBe(10);
        });

        it('paginates articles with status filter', function () {
            Article::factory()->count(5)->create(['status' => ArticleStatus::PUBLISHED]);
            Article::factory()->count(3)->create(['status' => ArticleStatus::DRAFT]);

            $result = $this->repository->paginate(10, ['status' => ArticleStatus::PUBLISHED->value]);

            expect($result->total())->toBe(5);
            foreach ($result->items() as $article) {
                expect($article->status)->toBe(ArticleStatus::PUBLISHED->value);
            }
        });

        it('paginates articles with type filter', function () {
            Article::factory()->count(4)->create(['type' => ArticleType::TUTORIAL]);
            Article::factory()->count(6)->create(['type' => ArticleType::ARTICLE]);

            $result = $this->repository->paginate(10, ['type' => ArticleType::TUTORIAL->value]);

            expect($result->total())->toBe(4);
            foreach ($result->items() as $article) {
                expect($article->type)->toBe(ArticleType::TUTORIAL->value);
            }
        });

        it('paginates articles with author_id filter', function () {
            $user1 = User::factory()->create();
            $user2 = User::factory()->create();

            Article::factory()->count(3)->create(['author_id' => $user1->_id]);
            Article::factory()->count(2)->create(['author_id' => $user2->_id]);

            $result = $this->repository->paginate(10, ['author_id' => $user1->_id]);

            expect($result->total())->toBe(3);
            foreach ($result->items() as $article) {
                expect($article->author_id)->toBe($user1->_id);
            }
        });

        it('paginates articles with featured filter', function () {
            Article::factory()->count(3)->create(['is_featured' => true]);
            Article::factory()->count(7)->create(['is_featured' => false]);

            $result = $this->repository->paginate(10, ['featured' => true]);

            expect($result->total())->toBe(3);
            foreach ($result->items() as $article) {
                expect($article->is_featured)->toBeTrue();
            }
        });

        it('paginates articles with multiple filters', function () {
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

            $result = $this->repository->paginate(10, $filters);

            expect($result->total())->toBe(2);
            foreach ($result->items() as $article) {
                expect($article->status)->toBe(ArticleStatus::PUBLISHED->value)
                    ->and($article->type)->toBe(ArticleType::ARTICLE->value)
                    ->and($article->author_id)->toBe($user->_id)
                    ->and($article->is_featured)->toBeTrue();
            }
        });
    });

    describe('getPublished method', function () {
        it('returns only published articles with published_at in past', function () {
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

            $result = $this->repository->getPublished();

            expect($result)->toBeInstanceOf(Collection::class)
                ->and($result->count())->toBe(3);

            foreach ($result as $article) {
                expect($article->status)->toBe('published')
                    ->and($article->published_at->isPast())->toBeTrue();
            }
        });
    });

    describe('getFeatured method', function () {
        it('returns only featured published articles', function () {
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

            $result = $this->repository->getFeatured();

            expect($result)->toBeInstanceOf(Collection::class)
                ->and($result->count())->toBe(2);

            foreach ($result as $article) {
                expect($article->is_featured)->toBeTrue()
                    ->and($article->status)->toBe('published');
            }
        });
    });

    describe('getByAuthor method', function () {
        it('returns articles by specific author', function () {
            $user1 = User::factory()->create();
            $user2 = User::factory()->create();

            Article::factory()->count(4)->create(['author_id' => $user1->_id]);
            Article::factory()->count(2)->create(['author_id' => $user2->_id]);

            $result = $this->repository->getByAuthor($user1->_id);

            expect($result)->toBeInstanceOf(Collection::class)
                ->and($result->count())->toBe(4);

            foreach ($result as $article) {
                expect($article->author_id)->toBe($user1->_id);
            }
        });
    });

    describe('getByType method', function () {
        it('returns only published articles of specific type', function () {
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

            $result = $this->repository->getByType(ArticleType::TUTORIAL->value);

            expect($result)->toBeInstanceOf(Collection::class)
                ->and($result->count())->toBe(3);

            foreach ($result as $article) {
                expect($article->type)->toBe(ArticleType::TUTORIAL->value)
                    ->and($article->status)->toBe('published');
            }
        });
    });

    describe('search method', function () {
        it('searches articles by term in title, content and excerpt', function () {
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

            $result = $this->repository->search('PHP');

            expect($result)->toBeInstanceOf(Collection::class)
                ->and($result->count())->toBe(3);
        });
    });

    describe('getByTags method', function () {
        it('returns articles by single tag', function () {
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

            $result = $this->repository->getByTags(['php']);

            expect($result)->toBeInstanceOf(Collection::class)
                ->and($result->count())->toBe(2);
        });

        it('returns articles by multiple tags', function () {
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

            $result = $this->repository->getByTags(['php', 'laravel']);

            expect($result)->toBeInstanceOf(Collection::class)
                ->and($result->count())->toBe(3);
        });

        it('returns published articles only when filtering by tags with empty array', function () {
            Article::factory()->count(5)->create(['status' => 'published']);
            Article::factory()->count(3)->create(['status' => 'draft']);

            $result = $this->repository->getByTags([]);

            expect($result)->toBeInstanceOf(Collection::class)
                ->and($result->count())->toBe(5);

            foreach ($result as $article) {
                expect($article->status)->toBe('published');
            }
        });
    });
});