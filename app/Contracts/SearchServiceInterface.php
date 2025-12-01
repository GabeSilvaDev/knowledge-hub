<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\Article;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Interface SearchServiceInterface.
 *
 * Defines the contract for article search services.
 */
interface SearchServiceInterface
{
    /**
     * Search articles by query term.
     *
     * @param  string  $query  The search term
     * @param  array<string, mixed>  $filters  Additional filters (author, tags, status, dates)
     * @param  int  $perPage  Number of results per page
     * @return LengthAwarePaginator<int, Article> The paginated search results
     */
    public function search(string $query, array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Get autocomplete suggestions based on the search term.
     *
     * @param  string  $query  The partial search term
     * @param  int  $limit  The maximum number of suggestions
     * @return array<int, array{id: string, title: string, slug: string, excerpt: string|null}> The autocomplete suggestions
     */
    public function autocomplete(string $query, int $limit = 10): array;

    /**
     * Map a collection of articles to the autocomplete format.
     *
     * @param  \Illuminate\Support\Collection<int, \App\Models\Article>  $articles  The articles to map
     * @return array<int, array{id: string, title: string, slug: string, excerpt: string|null}> The mapped autocomplete data
     */
    public function mapArticlesToAutocomplete($articles): array;

    /**
     * Synchronize all articles with the search index.
     *
     * @return int The number of indexed articles
     */
    public function syncAll(): int;

    /**
     * Remove an article from the search index.
     *
     * @param  string  $articleId  The article ID to remove
     * @return bool True if removed successfully
     */
    public function removeFromIndex(string $articleId): bool;
}
