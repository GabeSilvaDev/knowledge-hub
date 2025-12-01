<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\ArticleRepositoryInterface;
use App\Contracts\SearchServiceInterface;
use App\Enums\ArticleStatus;
use App\Models\Article;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Search Service.
 *
 * Implements article search logic using Laravel Scout and Meilisearch.
 */
class SearchService implements SearchServiceInterface
{
    /**
     * Initialize the Search Service.
     *
     * @param  ArticleRepositoryInterface  $articleRepository  Repository for article data access
     */
    public function __construct(
        private readonly ArticleRepositoryInterface $articleRepository
    ) {}

    /**
     * Search articles by query term.
     *
     * @param  string  $query  The search term
     * @param  array<string, mixed>  $filters  Additional filters (author, tags, status, date_from, date_to)
     * @param  int  $perPage  Number of results per page
     * @return LengthAwarePaginator<int, Article> The paginated search results
     */
    public function search(string $query, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $searchQuery = Article::search($query);

        $searchQuery->query(fn($builder) => $this->applyFilters($builder, $filters));

        return $searchQuery->paginate($perPage);
    }

    /**
     * Apply filters to the search query.
     *
     * @param  mixed  $builder  The query builder instance
     * @param  array<string, mixed>  $filters  The filters to apply
     * @return mixed The query builder with filters applied
     */
    private function applyFilters($builder, array $filters)
    {
        if (isset($filters['author_id']) && $filters['author_id'] !== '') {
            /* @phpstan-ignore method.nonObject */
            $builder->where('author_id', $filters['author_id']);
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            /* @phpstan-ignore method.nonObject */
            $builder->where('status', $filters['status']);
        } else {
            /* @phpstan-ignore method.nonObject */
            $builder->where('status', ArticleStatus::PUBLISHED->value);
        }

        if (isset($filters['type']) && $filters['type'] !== '') {
            /* @phpstan-ignore method.nonObject */
            $builder->where('type', $filters['type']);
        }

        if (isset($filters['tags']) && is_array($filters['tags']) && count($filters['tags']) > 0) {
            /* @phpstan-ignore method.nonObject */
            $builder->whereIn('tags', $filters['tags']);
        }

        if (isset($filters['categories']) && is_array($filters['categories']) && count($filters['categories']) > 0) {
            /* @phpstan-ignore method.nonObject */
            $builder->whereIn('categories', $filters['categories']);
        }

        if (isset($filters['date_from']) && $filters['date_from'] !== '') {
            /* @phpstan-ignore method.nonObject */
            $builder->where('published_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to']) && $filters['date_to'] !== '') {
            /* @phpstan-ignore method.nonObject */
            $builder->where('published_at', '<=', $filters['date_to']);
        }

        return $builder;
    }

    /**
     * Get autocomplete suggestions based on the search term.
     *
     * @param  string  $query  The partial search term
     * @param  int  $limit  The maximum number of suggestions
     * @return array<int, array{id: string, title: string, slug: string, excerpt: string|null}> The autocomplete suggestions
     */
    public function autocomplete(string $query, int $limit = 10): array
    {
        if (strlen($query) < 2) {
            return [];
        }

        $results = Article::search($query)
            /* @phpstan-ignore method.nonObject */
            ->query(fn($builder) => $builder->where('status', ArticleStatus::PUBLISHED->value))
            ->take($limit)
            ->get();

        return $this->mapArticlesToAutocomplete($results);
    }

    /**
     * Map a collection of articles to the autocomplete format.
     *
     * @param  \Illuminate\Support\Collection<int, Article>  $articles  The articles to map
     * @return array<int, array{id: string, title: string, slug: string, excerpt: string|null}> The mapped autocomplete data
     */
    public function mapArticlesToAutocomplete($articles): array
    {
        /** @var array<int, array{id: string, title: string, slug: string, excerpt: string|null}> $mapped */
        $mapped = $articles->map(function (Article $article): array {
            $id = $article->id;
            $idString = is_string($id) ? $id : '';

            return [
                'id' => $idString,
                'title' => $article->title,
                'slug' => $article->slug,
                'excerpt' => $article->excerpt ? substr((string) $article->excerpt, 0, 100) : null,
            ];
        })->toArray();

        return $mapped;
    }

    /**
     * Synchronize all articles with the search index.
     *
     * @return int The number of indexed articles
     */
    public function syncAll(): int
    {
        $count = Article::count();

        Article::makeAllSearchable();

        return $count;
    }

    /**
     * Remove an article from the search index.
     *
     * @param  string  $articleId  The article ID to remove
     * @return bool True if removed successfully, false otherwise
     */
    public function removeFromIndex(string $articleId): bool
    {
        $article = $this->articleRepository->findById($articleId);

        if (! $article instanceof \App\Models\Article) {
            return false;
        }

        $article->unsearchable();

        return true;
    }
}
