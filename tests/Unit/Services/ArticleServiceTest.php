<?php

use App\Cache\RedisCacheKeyGenerator;
use App\DTOs\CreateArticleDTO;
use App\Enums\ArticleStatus;
use App\Enums\ArticleType;
use App\Models\Article;
use App\Models\User;
use App\Repositories\ArticleRepository;
use App\Services\ArticleService;
use Illuminate\Pagination\LengthAwarePaginator;

describe('ArticleService', function (): void {
    beforeEach(function (): void {
        $this->repository = new ArticleRepository(new Article);
        $this->keyGenerator = new RedisCacheKeyGenerator;
        $this->service = new ArticleService($this->repository, $this->keyGenerator);
    });

    afterEach(function (): void {
        Article::query()->delete();
        User::query()->delete();
    });

    describe('constructor', function (): void {
        it('creates service with repository dependency', function (): void {
            expect($this->service)->toBeInstanceOf(ArticleService::class);
        });
    });

    describe('createArticle method', function (): void {
        it('creates article with minimal data', function (): void {
            $user = User::factory()->create();

            $dto = CreateArticleDTO::fromArray([
                'title' => 'Test Article',
                'content' => 'This is test content for the article.',
                'author_id' => $user->_id,
                'status' => ArticleStatus::DRAFT->value,
                'type' => ArticleType::ARTICLE->value,
            ]);

            $result = $this->service->createArticle($dto);

            expect($result)->toBeInstanceOf(Article::class)
                ->and($result->title)->toBe('Test Article')
                ->and($result->content)->toBe('This is test content for the article.')
                ->and($result->author_id)->toBe($user->_id)
                ->and($result->status)->toBe(ArticleStatus::DRAFT->value)
                ->and($result->type)->toBe(ArticleType::ARTICLE->value);
        });

        it('creates article with all data including SEO', function (): void {
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

            $result = $this->service->createArticle($dto);

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
            $user = User::factory()->create();

            $dto = CreateArticleDTO::fromArray([
                'title' => 'Article Without Slug',
                'content' => 'Content for article without slug.',
                'author_id' => $user->_id,
                'status' => ArticleStatus::DRAFT->value,
                'type' => ArticleType::ARTICLE->value,
            ]);

            $result = $this->service->createArticle($dto);

            expect($result)->toBeInstanceOf(Article::class)
                ->and($result->title)->toBe('Article Without Slug')
                ->and($result->slug)->not->toBeEmpty();
        });

        it('creates article with auto-generated excerpt when not provided', function (): void {
            $user = User::factory()->create();
            $longContent = str_repeat('This is a long content. ', 50);

            $dto = CreateArticleDTO::fromArray([
                'title' => 'Article With Long Content',
                'content' => $longContent,
                'author_id' => $user->_id,
                'status' => ArticleStatus::DRAFT->value,
                'type' => ArticleType::ARTICLE->value,
            ]);

            $result = $this->service->createArticle($dto);

            expect($result)->toBeInstanceOf(Article::class)
                ->and($result->excerpt)->not->toBeEmpty()
                ->and($result->excerpt)->toEndWith('...');
        });

        it('creates article with reading time calculation', function (): void {
            $user = User::factory()->create();
            $content = str_repeat('word ', 300);

            $dto = CreateArticleDTO::fromArray([
                'title' => 'Article With Reading Time',
                'content' => $content,
                'author_id' => $user->_id,
                'status' => ArticleStatus::DRAFT->value,
                'type' => ArticleType::ARTICLE->value,
            ]);

            $result = $this->service->createArticle($dto);

            expect($result)->toBeInstanceOf(Article::class)
                ->and($result->reading_time)->toBeGreaterThan(0);
        });

        it('creates article with SEO data when provided', function (): void {
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

            $result = $this->service->createArticle($dto);

            expect($result)->toBeInstanceOf(Article::class)
                ->and($result->title)->toBe('SEO Article');
        });

        it('creates article with partial SEO data', function (): void {
            $user = User::factory()->create();

            $dto = CreateArticleDTO::fromArray([
                'title' => 'Partial SEO Article',
                'content' => 'Article with partial SEO data.',
                'author_id' => $user->_id,
                'status' => ArticleStatus::PUBLISHED->value,
                'type' => ArticleType::ARTICLE->value,
                'seo_title' => 'Only SEO Title',
            ]);

            $result = $this->service->createArticle($dto);

            expect($result)->toBeInstanceOf(Article::class)
                ->and($result->title)->toBe('Partial SEO Article');
        });

        it('generates slug automatically when not provided in DTO', function (): void {
            $user = User::factory()->create();

            $dto = CreateArticleDTO::fromArray([
                'title' => 'Test Article With Empty Fields',
                'content' => 'Short content.',
                'author_id' => $user->_id,
                'status' => ArticleStatus::DRAFT->value,
                'type' => ArticleType::ARTICLE->value,
            ]);

            $result = $this->service->createArticle($dto);

            expect($result)->toBeInstanceOf(Article::class)
                ->and($result->slug)->not->toBeEmpty()
                ->and($result->slug)->toContain('test-article');
        });

        it('generates excerpt automatically for short content', function (): void {
            $user = User::factory()->create();

            $dto = CreateArticleDTO::fromArray([
                'title' => 'Short Content Test',
                'content' => 'Short',
                'author_id' => $user->_id,
                'status' => ArticleStatus::DRAFT->value,
                'type' => ArticleType::ARTICLE->value,
            ]);

            $result = $this->service->createArticle($dto);

            expect($result)->toBeInstanceOf(Article::class)
                ->and($result->excerpt)->toBe('Short')
                ->and($result->excerpt)->not->toContain('...');
        });

        it('generates excerpt when not provided in DTO', function (): void {
            $user = User::factory()->create();

            $dto = CreateArticleDTO::fromArray([
                'title' => 'Article Without Excerpt',
                'content' => 'This is test content for the article that will be used to generate an excerpt.',
                'author_id' => $user->_id,
                'status' => ArticleStatus::DRAFT->value,
                'type' => ArticleType::ARTICLE->value,
            ]);

            $result = $this->service->createArticle($dto);

            expect($result)->toBeInstanceOf(Article::class)
                ->and($result->excerpt)->not->toBeEmpty()
                ->and($result->excerpt)->toBeString();
        });

        it('generates excerpt without ellipsis for short content', function (): void {
            $user = User::factory()->create();
            $shortContent = 'Short content';

            $dto = CreateArticleDTO::fromArray([
                'title' => 'Article With Short Content',
                'content' => $shortContent,
                'author_id' => $user->_id,
                'status' => ArticleStatus::DRAFT->value,
                'type' => ArticleType::ARTICLE->value,
            ]);

            $result = $this->service->createArticle($dto);

            expect($result)->toBeInstanceOf(Article::class)
                ->and($result->excerpt)->toBe($shortContent)
                ->and($result->excerpt)->not->toContain('...');
        });
    });

    describe('query method', function (): void {
        it('returns QueryBuilder instance', function (): void {
            $result = $this->service->query();

            expect($result)->toBeInstanceOf(\Spatie\QueryBuilder\QueryBuilder::class);
        });

        it('can filter articles by status through service', function (): void {
            Article::factory()->count(5)->create(['status' => ArticleStatus::PUBLISHED->value]);
            Article::factory()->count(3)->create(['status' => ArticleStatus::DRAFT->value]);

            $result = $this->service->query()
                ->where('status', ArticleStatus::PUBLISHED->value)
                ->get();

            expect($result)->toHaveCount(5);
        });

        it('can paginate articles through service', function (): void {
            Article::factory()->count(25)->create();

            $result = $this->service->query()->paginate(10);

            expect($result)->toBeInstanceOf(LengthAwarePaginator::class)
                ->and($result->perPage())->toBe(10)
                ->and($result->total())->toBe(25);
        });

        it('can filter and paginate together', function (): void {
            Article::factory()->count(15)->create(['status' => ArticleStatus::PUBLISHED->value]);
            Article::factory()->count(10)->create(['status' => ArticleStatus::DRAFT->value]);

            $result = $this->service->query()
                ->where('status', ArticleStatus::PUBLISHED->value)
                ->paginate(5);

            expect($result)->toBeInstanceOf(LengthAwarePaginator::class)
                ->and($result->perPage())->toBe(5)
                ->and($result->total())->toBe(15);
        });
    });

    describe('updateArticle method', function (): void {
        it('updates article with new data', function (): void {
            $article = Article::factory()->create();
            $newData = [
                'title' => 'Updated Title',
                'content' => 'Updated content.',
            ];

            $updatedArticle = $this->service->updateArticle($article, $newData);

            expect($updatedArticle->title)->toBe('Updated Title')
                ->and($updatedArticle->content)->toBe('Updated content.');
        });

        it('updates reading_time when content is updated', function (): void {
            $article = Article::factory()->create(['content' => 'Short content.']);
            $newContent = str_repeat('word ', 500);

            $updatedArticle = $this->service->updateArticle($article, ['content' => $newContent]);

            expect($updatedArticle->reading_time)->toBe(3);
        });

        it('generates excerpt when content is updated and excerpt is empty', function (): void {
            $article = Article::factory()->create(['content' => 'Initial content.']);
            $newContent = str_repeat('This is a long new content. ', 20);

            $updatedArticle = $this->service->updateArticle($article, ['content' => $newContent]);

            expect($updatedArticle->excerpt)->not->toBeEmpty()
                ->and($updatedArticle->excerpt)->toEndWith('...');
        });

        it('does not generate excerpt if content is not updated', function (): void {
            $initialExcerpt = 'Initial excerpt.';
            $article = Article::factory()->create(['excerpt' => $initialExcerpt]);

            $updatedArticle = $this->service->updateArticle($article, ['title' => 'New Title']);

            expect($updatedArticle->excerpt)->toBe($initialExcerpt);
        });

        it('generates slug when title is updated and slug is empty', function (): void {
            $article = Article::factory()->create(['title' => 'Initial Title']);
            $newTitle = 'A Brand New Title for Article';

            $updatedArticle = $this->service->updateArticle($article, ['title' => $newTitle]);

            expect($updatedArticle->slug)->toBe('a-brand-new-title-for-article');
        });

        it('does not generate slug if title is not updated', function (): void {
            $initialSlug = 'initial-slug';
            $article = Article::factory()->create(['slug' => $initialSlug]);

            $updatedArticle = $this->service->updateArticle($article, ['content' => 'New content.']);

            expect($updatedArticle->slug)->toBe($initialSlug);
        });

        it('updates article without changing slug if new title is provided but slug is also provided', function (): void {
            $article = Article::factory()->create();
            $newData = [
                'title' => 'Updated Title With Slug',
                'slug' => 'custom-slug-provided',
            ];

            $updatedArticle = $this->service->updateArticle($article, $newData);

            expect($updatedArticle->title)->toBe('Updated Title With Slug')
                ->and($updatedArticle->slug)->toBe('custom-slug-provided');
        });

        it('updates article without changing excerpt if new content is provided but excerpt is also provided', function (): void {
            $article = Article::factory()->create();
            $newData = [
                'content' => 'Updated content with custom excerpt.',
                'excerpt' => 'My custom excerpt.',
            ];

            $updatedArticle = $this->service->updateArticle($article, $newData);

            expect($updatedArticle->content)->toBe('Updated content with custom excerpt.')
                ->and($updatedArticle->excerpt)->toBe('My custom excerpt.');
        });
    });

    describe('deleteArticle method', function (): void {
        it('deletes an article', function (): void {
            $article = Article::factory()->create();

            $result = $this->service->deleteArticle($article);

            expect($result)->toBeTrue();
            expect(Article::find($article->_id))->toBeNull();
        });
    });
});
