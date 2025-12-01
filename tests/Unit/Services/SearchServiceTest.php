<?php

use App\Contracts\ArticleRepositoryInterface;
use App\Enums\ArticleStatus;
use App\Enums\ArticleType;
use App\Models\Article;
use App\Services\SearchService;

beforeEach(function (): void {
    $this->mockArticleRepository = Mockery::mock(ArticleRepositoryInterface::class);
    $this->searchService = new SearchService($this->mockArticleRepository);
});

describe('SearchService', function (): void {
    describe('search method', function (): void {
        it('searches articles with query only', function (): void {
            Article::factory()->count(3)->create([
                'status' => ArticleStatus::PUBLISHED->value,
                'title' => 'Laravel Testing',
            ]);

            Article::makeAllSearchable();

            $results = $this->searchService->search('Laravel');

            expect($results)->toHaveKey('data')
                ->and($results)->toHaveKey('total');
        });

        it('searches articles with author filter', function (): void {
            $user = \App\Models\User::factory()->create();

            Article::factory()->count(2)->create([
                'status' => ArticleStatus::PUBLISHED->value,
                'author_id' => $user->id,
            ]);

            Article::makeAllSearchable();

            $results = $this->searchService->search('test', ['author_id' => $user->id]);

            expect($results)->toHaveKey('data');
        });

        it('searches articles with status filter', function (): void {
            Article::factory()->create([
                'status' => ArticleStatus::DRAFT->value,
            ]);

            Article::makeAllSearchable();

            $results = $this->searchService->search('test', ['status' => ArticleStatus::DRAFT->value]);

            expect($results)->toHaveKey('data');
        });

        it('searches articles with type filter', function (): void {
            Article::factory()->create([
                'status' => ArticleStatus::PUBLISHED->value,
                'type' => ArticleType::TUTORIAL->value,
            ]);

            Article::makeAllSearchable();

            $results = $this->searchService->search('test', ['type' => ArticleType::TUTORIAL->value]);

            expect($results)->toHaveKey('data');
        });

        it('searches articles with tags filter', function (): void {
            Article::factory()->create([
                'status' => ArticleStatus::PUBLISHED->value,
                'tags' => ['laravel', 'php'],
            ]);

            Article::makeAllSearchable();

            $results = $this->searchService->search('test', ['tags' => ['laravel']]);

            expect($results)->toHaveKey('data');
        });

        it('searches articles with categories filter', function (): void {
            Article::factory()->create([
                'status' => ArticleStatus::PUBLISHED->value,
                'categories' => ['web-development', 'backend'],
            ]);

            Article::makeAllSearchable();

            $results = $this->searchService->search('test', ['categories' => ['web-development']]);

            expect($results)->toHaveKey('data');
        });

        it('searches articles with date_from filter', function (): void {
            Article::factory()->create([
                'status' => ArticleStatus::PUBLISHED->value,
                'published_at' => now()->subDays(5),
            ]);

            Article::makeAllSearchable();

            $results = $this->searchService->search('test', ['date_from' => now()->subDays(10)->toDateString()]);

            expect($results)->toHaveKey('data');
        });

        it('searches articles with date_to filter', function (): void {
            Article::factory()->create([
                'status' => ArticleStatus::PUBLISHED->value,
                'published_at' => now()->subDays(5),
            ]);

            Article::makeAllSearchable();

            $results = $this->searchService->search('test', ['date_to' => now()->toDateString()]);

            expect($results)->toHaveKey('data');
        });

        it('searches with custom per page', function (): void {
            Article::factory()->count(20)->create([
                'status' => ArticleStatus::PUBLISHED->value,
            ]);

            Article::makeAllSearchable();

            $results = $this->searchService->search('test', [], 5);

            expect($results->perPage())->toBe(5);
        });

        it('filters published articles by default when no status provided', function (): void {
            Article::factory()->create(['status' => ArticleStatus::PUBLISHED->value]);
            Article::factory()->create(['status' => ArticleStatus::DRAFT->value]);

            Article::makeAllSearchable();

            $results = $this->searchService->search('test', []);

            expect($results)->toHaveKey('data');
        });

        it('applies empty string filters correctly', function (): void {
            Article::factory()->create(['status' => ArticleStatus::PUBLISHED->value]);

            Article::makeAllSearchable();

            $results = $this->searchService->search('test', [
                'author_id' => '',
                'status' => '',
                'type' => '',
                'date_from' => '',
                'date_to' => '',
            ]);

            expect($results)->toHaveKey('data');
        });

        it('handles empty tags array', function (): void {
            Article::factory()->create(['status' => ArticleStatus::PUBLISHED->value]);

            Article::makeAllSearchable();

            $results = $this->searchService->search('test', ['tags' => []]);

            expect($results)->toHaveKey('data');
        });

        it('handles empty categories array', function (): void {
            Article::factory()->create(['status' => ArticleStatus::PUBLISHED->value]);

            Article::makeAllSearchable();

            $results = $this->searchService->search('test', ['categories' => []]);

            expect($results)->toHaveKey('data');
        });
    });

    describe('autocomplete method', function (): void {
        it('returns empty array for short query', function (): void {
            $results = $this->searchService->autocomplete('a');

            expect($results)->toBeArray()->toBeEmpty();
        });

        it('returns autocomplete suggestions for valid query', function (): void {
            Article::factory()->create([
                'status' => ArticleStatus::PUBLISHED->value,
                'title' => 'Laravel Testing Guide',
                'excerpt' => 'A comprehensive guide to testing in Laravel framework',
            ]);

            Article::makeAllSearchable();

            $results = $this->searchService->autocomplete('Laravel');

            expect($results)->toBeArray();
        });

        it('respects limit parameter', function (): void {
            Article::factory()->count(20)->create([
                'status' => ArticleStatus::PUBLISHED->value,
                'title' => 'Laravel Article',
            ]);

            Article::makeAllSearchable();

            $results = $this->searchService->autocomplete('Laravel', 5);

            expect($results)->toBeArray()
                ->and(count($results))->toBeLessThanOrEqual(5);
        });

        it('handles articles with null excerpt', function (): void {
            Article::factory()->create([
                'status' => ArticleStatus::PUBLISHED->value,
                'title' => 'No Excerpt Article',
                'excerpt' => null,
            ]);

            Article::makeAllSearchable();

            $results = $this->searchService->autocomplete('No Excerpt');

            expect($results)->toBeArray();
        });

        it('only returns published articles', function (): void {
            Article::factory()->create([
                'status' => ArticleStatus::DRAFT->value,
                'title' => 'Draft Article',
            ]);

            Article::factory()->create([
                'status' => ArticleStatus::PUBLISHED->value,
                'title' => 'Published Article',
            ]);

            Article::makeAllSearchable();

            $results = $this->searchService->autocomplete('Article');

            expect($results)->toBeArray();
        });
    });

    describe('syncAll method', function (): void {
        it('syncs all articles to search index', function (): void {
            Article::factory()->count(5)->create();

            $count = $this->searchService->syncAll();

            expect($count)->toBeGreaterThanOrEqual(5);
        });

        it('returns correct count of synced articles', function (): void {
            $initialCount = Article::count();
            Article::factory()->count(3)->create();

            $count = $this->searchService->syncAll();

            expect($count)->toBe($initialCount + 3);
        });
    });

    describe('removeFromIndex method', function (): void {
        it('removes article from search index', function (): void {
            $article = Article::factory()->create();
            $article->searchable();

            $this->mockArticleRepository->shouldReceive('findById')
                ->with((string) $article->id)
                ->andReturn($article);

            $result = $this->searchService->removeFromIndex((string) $article->id);

            expect($result)->toBeTrue();
        });

        it('returns false when article not found', function (): void {
            $this->mockArticleRepository->shouldReceive('findById')
                ->with('non-existent-id')
                ->andReturn(null);

            $result = $this->searchService->removeFromIndex('non-existent-id');

            expect($result)->toBeFalse();
        });
    });

    describe('autocomplete map function coverage', function (): void {
        it('maps article data correctly with string id', function (): void {
            $user = \App\Models\User::factory()->create();
            $article = Article::factory()->create([
                'status' => ArticleStatus::PUBLISHED->value,
                'title' => 'Test Article Title',
                'slug' => 'test-article-title',
                'excerpt' => 'Short excerpt text',
                'author_id' => $user->id,
            ]);

            $articles = collect([$article]);

            $results = $this->searchService->mapArticlesToAutocomplete($articles);

            expect($results)->toBeArray()
                ->and($results)->toHaveCount(1)
                ->and($results[0])->toHaveKeys(['id', 'title', 'slug', 'excerpt'])
                ->and($results[0]['id'])->toBeString()
                ->and($results[0]['title'])->toBe('Test Article Title')
                ->and($results[0]['slug'])->toBe('test-article-title')
                ->and($results[0]['excerpt'])->toBe('Short excerpt text');
        });

        it('maps article with null excerpt correctly', function (): void {
            $user = \App\Models\User::factory()->create();
            $article = Article::factory()->create([
                'status' => ArticleStatus::PUBLISHED->value,
                'title' => 'No Excerpt Test',
                'slug' => 'no-excerpt-test',
                'excerpt' => null,
                'author_id' => $user->id,
            ]);

            $articles = collect([$article]);

            $results = $this->searchService->mapArticlesToAutocomplete($articles);

            expect($results[0]['excerpt'])->toBeNull();
        });

        it('truncates long excerpt to 100 characters', function (): void {
            $user = \App\Models\User::factory()->create();
            $longExcerpt = str_repeat('This is a long excerpt. ', 10);
            $article = Article::factory()->create([
                'status' => ArticleStatus::PUBLISHED->value,
                'title' => 'Long Excerpt Test',
                'slug' => 'long-excerpt-test',
                'excerpt' => $longExcerpt,
                'author_id' => $user->id,
            ]);

            $articles = collect([$article]);

            $results = $this->searchService->mapArticlesToAutocomplete($articles);

            expect(strlen((string) $results[0]['excerpt']))->toBe(100);
        });

        it('handles non-string id by converting to empty string', function (): void {
            $user = \App\Models\User::factory()->create();
            $article = Article::factory()->create([
                'status' => ArticleStatus::PUBLISHED->value,
                'author_id' => $user->id,
            ]);

            $articles = collect([$article]);

            $results = $this->searchService->mapArticlesToAutocomplete($articles);

            expect($results[0]['id'])->toBeString();
        });
    });
});
