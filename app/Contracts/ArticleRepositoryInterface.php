<?php

namespace App\Contracts;

use App\DTOs\CreateArticleDTO;
use App\Models\Article;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ArticleRepositoryInterface
{
    /**
     * Find article by ID.
     */
    public function findById(string $id): ?Article;

    /**
     * Find article by slug.
     */
    public function findBySlug(string $slug): ?Article;

    /**
     * Create a new article.
     */
    public function create(CreateArticleDTO $dto): Article;

    /**
     * Get paginated articles.
     */
    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator;

    /**
     * Get published articles.
     */
    public function getPublished(): Collection;

    /**
     * Get featured articles.
     */
    public function getFeatured(): Collection;

    /**
     * Get articles by author.
     */
    public function getByAuthor(string $authorId): Collection;

    /**
     * Get articles by type.
     */
    public function getByType(string $type): Collection;

    /**
     * Search articles.
     */
    public function search(string $term): Collection;

    /**
     * Get articles by tags.
     */
    public function getByTags(array $tags): Collection;
}
