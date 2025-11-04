<?php

use App\DTOs\CreateArticleDTO;
use App\Enums\ArticleStatus;
use App\Enums\ArticleType;
use App\Exceptions\ArticleRefreshException;
use App\Models\Article;
use App\Models\User;
use App\Repositories\ArticleRepository;
use Illuminate\Pagination\LengthAwarePaginator;

use function Pest\Laravel\mock;

use Spatie\QueryBuilder\QueryBuilder;

describe('ArticleRepository', function (): void {
    beforeEach(function (): void {
        $this->repository = new ArticleRepository(new Article);
    });

    afterEach(function (): void {
        Article::query()->delete();
        User::query()->delete();
    });

    describe('constructor', function (): void {
        it('creates repository with Article model dependency', function (): void {
            expect($this->repository)->toBeInstanceOf(ArticleRepository::class);
        });
    });

    describe('query method', function (): void {
        it('returns QueryBuilder instance', function (): void {
            $result = $this->repository->query();

            expect($result)->toBeInstanceOf(QueryBuilder::class);
        });

        it('can filter articles by status', function (): void {
            Article::factory()->count(5)->create(['status' => ArticleStatus::PUBLISHED->value]);
            Article::factory()->count(3)->create(['status' => ArticleStatus::DRAFT->value]);

            $result = $this->repository->query()
                ->where('status', ArticleStatus::PUBLISHED->value)
                ->get();

            expect($result)->toHaveCount(5);
        });

        it('can filter articles by type', function (): void {
            Article::factory()->count(4)->create(['type' => ArticleType::TUTORIAL->value]);
            Article::factory()->count(6)->create(['type' => ArticleType::ARTICLE->value]);

            $result = $this->repository->query()
                ->where('type', ArticleType::TUTORIAL->value)
                ->get();

            expect($result)->toHaveCount(4);
        });
    });

    describe('create method', function (): void {
        it('creates article from DTO', function (): void {
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

    describe('update method', function (): void {
        it('updates article successfully', function (): void {
            $article = Article::factory()->create([
                'title' => 'Original Title',
                'content' => 'Original Content',
            ]);

            $result = $this->repository->update($article, [
                'title' => 'Updated Title',
                'content' => 'Updated Content',
            ]);

            expect($result)->toBeInstanceOf(Article::class)
                ->and($result->title)->toBe('Updated Title')
                ->and($result->content)->toBe('Updated Content')
                ->and($result->_id)->toBe($article->_id);
        });

        it('throws exception when fresh article is null', function (): void {
            $article = Article::factory()->create();

            $article->disableVersioning();

            $articleMock = mock(Article::class)->makePartial();
            $articleMock->shouldReceive('update')->andReturn(true);
            $articleMock->shouldReceive('fresh')->andReturn(null);
            $articleMock->shouldReceive('disableVersioning')->andReturnSelf();
            $articleMock->shouldReceive('enableVersioning')->andReturnSelf();
            $articleMock->_id = $article->_id;

            expect(fn () => $this->repository->update($articleMock, ['title' => 'New Title']))
                ->toThrow(ArticleRefreshException::class);
        });
    });

    describe('delete method', function (): void {
        it('deletes article successfully', function (): void {
            $article = Article::factory()->create();
            $articleId = $article->_id;

            $result = $this->repository->delete($article);

            expect($result)->toBeTrue()
                ->and(Article::find($articleId))->toBeNull();
        });

        it('returns true when article is deleted', function (): void {
            $article = Article::factory()->create();

            $result = $this->repository->delete($article);

            expect($result)->toBe(true);
        });
    });

    it('can paginate articles without filters', function (): void {
        Article::factory()->count(25)->create();

        $result = $this->repository->query()->paginate(10);

        expect($result)->toBeInstanceOf(LengthAwarePaginator::class)
            ->and($result->perPage())->toBe(10)
            ->and($result->total())->toBe(25)
            ->and(count($result->items()))->toBe(10);
    });

    it('can filter by status', function (): void {
        Article::factory()->count(5)->create(['status' => ArticleStatus::PUBLISHED->value]);
        Article::factory()->count(3)->create(['status' => ArticleStatus::DRAFT->value]);

        $result = $this->repository->query()
            ->where('status', ArticleStatus::PUBLISHED->value)
            ->get();

        expect($result)->toHaveCount(5);
        foreach ($result as $article) {
            expect($article->status)->toBe(ArticleStatus::PUBLISHED->value);
        }
    });

    it('can filter by type', function (): void {
        Article::factory()->count(4)->create(['type' => ArticleType::TUTORIAL->value]);
        Article::factory()->count(6)->create(['type' => ArticleType::ARTICLE->value]);

        $result = $this->repository->query()
            ->where('type', ArticleType::TUTORIAL->value)
            ->get();

        expect($result)->toHaveCount(4);
        foreach ($result as $article) {
            expect($article->type)->toBe(ArticleType::TUTORIAL->value);
        }
    });

    it('can filter by author_id', function (): void {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        Article::factory()->count(3)->create(['author_id' => $user1->_id]);
        Article::factory()->count(2)->create(['author_id' => $user2->_id]);

        $result = $this->repository->query()
            ->where('author_id', $user1->_id)
            ->get();

        expect($result)->toHaveCount(3);
        foreach ($result as $article) {
            expect($article->author_id)->toBe($user1->_id);
        }
    });

    it('can filter by featured status', function (): void {
        Article::factory()->count(3)->create(['is_featured' => true]);
        Article::factory()->count(7)->create(['is_featured' => false]);

        $result = $this->repository->query()
            ->where('is_featured', true)
            ->get();

        expect($result)->toHaveCount(3);
        foreach ($result as $article) {
            expect($article->is_featured)->toBeTrue();
        }
    });

    it('can filter by tags', function (): void {
        Article::factory()->create(['tags' => ['php', 'web']]);
        Article::factory()->create(['tags' => ['javascript', 'frontend']]);
        Article::factory()->create(['tags' => ['php', 'laravel']]);

        $result = Article::query()->tags(['php'])->get();

        expect($result)->toHaveCount(2);
    });

    it('can combine multiple filters', function (): void {
        $user = User::factory()->create();

        Article::factory()->count(2)->create([
            'status' => ArticleStatus::PUBLISHED->value,
            'type' => ArticleType::ARTICLE->value,
            'author_id' => $user->_id,
            'is_featured' => true,
        ]);

        Article::factory()->count(3)->create([
            'status' => ArticleStatus::DRAFT->value,
            'type' => ArticleType::ARTICLE->value,
            'author_id' => $user->_id,
            'is_featured' => true,
        ]);

        $result = $this->repository->query()
            ->where('status', ArticleStatus::PUBLISHED->value)
            ->where('type', ArticleType::ARTICLE->value)
            ->where('author_id', $user->_id)
            ->where('is_featured', true)
            ->get();

        expect($result)->toHaveCount(2);
        foreach ($result as $article) {
            expect($article->status)->toBe(ArticleStatus::PUBLISHED->value)
                ->and($article->type)->toBe(ArticleType::ARTICLE->value)
                ->and($article->author_id)->toBe($user->_id)
                ->and($article->is_featured)->toBeTrue();
        }
    });
});
