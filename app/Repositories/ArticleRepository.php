<?php

namespace App\Repositories;

use App\Contracts\ArticleRepositoryInterface;
use App\DTOs\CreateArticleDTO;
use App\Exceptions\ArticleRefreshException;
use App\Models\Article;
use Illuminate\Database\Eloquent\Collection;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ArticleRepository implements ArticleRepositoryInterface
{
    /**
     * Initialize the Article Repository.
     *
     * Constructs the repository with the Article model instance.
     *
     * @param  Article  $model  The Article model instance
     */
    public function __construct(
        private readonly Article $model
    ) {}

    /**
     * Get query builder for articles.
     *
     * Returns a Spatie QueryBuilder configured with allowed filters, sorts, and includes.
     *
     * @return QueryBuilder<Article> Configured query builder instance
     */
    public function query(): QueryBuilder
    {
        return QueryBuilder::for(Article::class)
            ->allowedFilters([
                AllowedFilter::exact('status'),
                AllowedFilter::exact('type'),
                AllowedFilter::exact('author_id'),
                AllowedFilter::exact('is_featured'),
                AllowedFilter::exact('is_pinned'),
                AllowedFilter::partial('title'),
                AllowedFilter::partial('content'),
                AllowedFilter::partial('excerpt'),
                AllowedFilter::scope('tags'),
                AllowedFilter::scope('categories'),
            ])
            ->allowedSorts([
                'title',
                'created_at',
                'updated_at',
                'published_at',
                'view_count',
                'like_count',
                'reading_time',
            ])
            ->allowedIncludes(['author'])
            ->defaultSort('-created_at');
    }

    /**
     * Create a new article.
     *
     * Persists a new article to the database using the provided DTO data.
     *
     * @param  CreateArticleDTO  $dto  Data transfer object containing article creation data
     * @return Article The newly created article instance
     */
    public function create(CreateArticleDTO $dto): Article
    {
        return $this->model->create($dto->toArray());
    }

    /**
     * Update an existing article.
     *
     * Updates the article with provided data and returns the refreshed instance.
     *
     * @param  Article  $article  The article instance to update
     * @param  array<string, mixed>  $data  The data to update the article with
     * @return Article The updated and refreshed article instance
     *
     * @throws ArticleRefreshException If article cannot be refreshed after update
     */
    public function update(Article $article, array $data): Article
    {
        $article->update($data);

        $freshArticle = $article->fresh();

        if ($freshArticle === null) {
            throw ArticleRefreshException::failedToRefresh();
        }

        return $freshArticle;
    }

    /**
     * Delete an article.
     *
     * Performs soft delete on the article.
     *
     * @param  Article  $article  The article instance to delete
     * @return bool True if deletion was successful
     */
    public function delete(Article $article): bool
    {
        return (bool) $article->delete();
    }

    /**
     * Get popular articles based on view count.
     *
     * Retrieves published articles with highest view counts within the specified time period.
     *
     * @param  int  $limit  Maximum number of articles to return (default: 10)
     * @param  int  $days  Time period in days to consider (default: 30)
     * @return Collection<int, Article> Collection of popular articles
     */
    public function getPopularArticles(int $limit = 10, int $days = 30): Collection
    {
        $startDate = now()->subDays($days);

        return Article::query()
            ->where('status', 'published')
            ->where('published_at', '>=', $startDate)
            ->orderBy('view_count', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Find multiple articles by IDs.
     *
     * Retrieves all articles matching the provided array of IDs.
     *
     * @param  array<int, string>  $ids  Array of article IDs to find
     * @return Collection<int, Article> Collection of found articles
     */
    public function findByIds(array $ids): Collection
    {
        return Article::query()
            ->whereIn('_id', $ids)
            ->get();
    }

    /**
     * Find article by ID.
     *
     * Retrieves a single article by its unique identifier.
     *
     * @param  string  $id  The article ID
     * @return Article|null The article instance or null if not found
     */
    public function findById(string $id): ?Article
    {
        return Article::query()->find($id);
    }

    /**
     * Get published articles with view count greater than zero.
     *
     * Retrieves all published articles that have at least one view.
     *
     * @return Collection<int, Article> Collection of published articles with views
     */
    public function getPublishedWithViews(): Collection
    {
        return Article::query()
            ->where('status', 'published')
            ->where('view_count', '>', 0)
            ->get();
    }

    /**
     * Check if slug exists (excluding specific ID).
     *
     * Verifies if a slug is already in use, optionally excluding a specific article ID.
     *
     * @param  string  $slug  The slug to check
     * @param  string|null  $excludeId  Optional article ID to exclude from check (for updates)
     * @return bool True if slug exists (excluding the excluded ID)
     */
    public function slugExists(string $slug, ?string $excludeId = null): bool
    {
        $query = Article::query()->where('slug', $slug);

        if ($excludeId !== null) {
            $query->where('_id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Load relationships for an article.
     *
     * Eagerly loads the specified relationships on the article instance.
     *
     * @param  Article  $article  The article instance to load relationships for
     * @param  array<int, string>  $relationships  Array of relationship names to load
     * @return Article The article with loaded relationships
     */
    public function loadRelationships(Article $article, array $relationships): Article
    {
        $article->load($relationships);

        return $article;
    }

    /**
     * Get published articles stats by author ID.
     *
     * Retrieves aggregated statistics for all published articles by a specific author.
     *
     * @param  string  $authorId  The author's user ID
     * @return array{articles_count: int, total_views: int, total_likes: int, total_comments: int} The article statistics
     */
    public function getPublishedArticleStatsByAuthor(string $authorId): array
    {
        $articles = Article::query()
            ->where('author_id', $authorId)
            ->where('status', 'published')
            ->get(['view_count', 'like_count', 'comment_count']);

        return [
            'articles_count' => $articles->count(),
            'total_views' => (int) $articles->sum('view_count'),
            'total_likes' => (int) $articles->sum('like_count'),
            'total_comments' => (int) $articles->sum('comment_count'),
        ];
    }
}
