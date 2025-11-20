<?php

namespace App\Contracts;

use App\DTOs\CreateArticleDTO;
use App\Models\Article;
use Illuminate\Database\Eloquent\Collection;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * Article repository contract.
 *
 * Defines the interface for article data access operations.
 */
interface ArticleRepositoryInterface
{
    /**
     * Get query builder for articles with filtering, sorting, and including.
     *
     * Returns a Spatie QueryBuilder instance configured with allowed filters,
     * sorts, and includes for advanced article queries.
     *
     * @return QueryBuilder<Article> Configured query builder instance
     */
    public function query(): QueryBuilder;

    /**
     * Create a new article.
     *
     * Persists a new article to the database using the provided DTO.
     *
     * @param  CreateArticleDTO  $dto  Data transfer object with article data
     * @return Article The newly created article instance
     */
    public function create(CreateArticleDTO $dto): Article;

    /**
     * Update an existing article.
     *
     * Updates the article with the provided data and returns refreshed instance.
     *
     * @param  Article  $article  The article to update
     * @param  array<string, mixed>  $data  The data to update
     * @return Article The updated article instance
     */
    public function update(Article $article, array $data): Article;

    /**
     * Delete an article (soft delete).
     *
     * Performs a soft delete operation on the article.
     *
     * @param  Article  $article  The article to delete
     * @return bool True if deletion was successful
     */
    public function delete(Article $article): bool;

    /**
     * Get popular articles based on view count.
     *
     * Retrieves published articles with highest view counts within time period.
     *
     * @param  int  $limit  Maximum number of articles (default: 10)
     * @param  int  $days  Time period in days (default: 30)
     * @return Collection<int, Article> Collection of popular articles
     */
    public function getPopularArticles(int $limit = 10, int $days = 30): Collection;

    /**
     * Find multiple articles by IDs.
     *
     * Retrieves all articles matching the provided array of IDs.
     *
     * @param  array<int, string>  $ids  Array of article IDs
     * @return Collection<int, Article> Collection of found articles
     */
    public function findByIds(array $ids): Collection;

    /**
     * Find article by ID.
     *
     * Retrieves a single article by its unique identifier.
     *
     * @param  string  $id  The article ID
     * @return Article|null The article or null if not found
     */
    public function findById(string $id): ?Article;

    /**
     * Get published articles with view count greater than zero.
     *
     * Retrieves all published articles that have at least one view.
     *
     * @return Collection<int, Article> Collection of published viewed articles
     */
    public function getPublishedWithViews(): Collection;

    /**
     * Check if slug exists (excluding specific ID).
     *
     * Verifies if a slug is already in use, optionally excluding an ID.
     *
     * @param  string  $slug  The slug to check
     * @param  string|null  $excludeId  Optional ID to exclude (for updates)
     * @return bool True if slug exists
     */
    public function slugExists(string $slug, ?string $excludeId = null): bool;

    /**
     * Load relationships for an article.
     *
     * Eagerly loads specified relationships on the article instance.
     *
     * @param  Article  $article  The article to load relationships for
     * @param  array<int, string>  $relationships  Array of relationship names
     * @return Article The article with loaded relationships
     */
    public function loadRelationships(Article $article, array $relationships): Article;
}
