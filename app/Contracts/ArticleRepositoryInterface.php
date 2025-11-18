<?php

namespace App\Contracts;

use App\DTOs\CreateArticleDTO;
use App\Models\Article;
use Illuminate\Database\Eloquent\Collection;
use Spatie\QueryBuilder\QueryBuilder;

interface ArticleRepositoryInterface
{
    /**
     * Get query builder for articles with filtering, sorting, and including.
     *
     * @return QueryBuilder<Article>
     */
    public function query(): QueryBuilder;

    /**
     * Create a new article.
     */
    public function create(CreateArticleDTO $dto): Article;

    /**
     * Update an existing article.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Article $article, array $data): Article;

    /**
     * Delete an article (soft delete).
     */
    public function delete(Article $article): bool;

    /**
     * Get popular articles based on view count.
     *
     * @return Collection<int, Article>
     */
    public function getPopularArticles(int $limit = 10, int $days = 30): Collection;
}
