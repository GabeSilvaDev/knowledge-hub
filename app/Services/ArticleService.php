<?php

namespace App\Services;

use App\Cache\RedisCacheKeyGenerator;
use App\Contracts\ArticleRepositoryInterface;
use App\DTOs\CreateArticleDTO;
use App\Models\Article;
use App\Models\ArticleVersion;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Spatie\QueryBuilder\QueryBuilder;

class ArticleService
{
    /**
     * Initialize the Article Service.
     *
     * Constructs the service with injected repository and cache dependencies.
     *
     * @param  ArticleRepositoryInterface  $articleRepository  Repository for article data access
     * @param  RedisCacheKeyGenerator  $cacheKeyGenerator  Generator for cache key management
     */
    public function __construct(
        private readonly ArticleRepositoryInterface $articleRepository,
        private readonly RedisCacheKeyGenerator $cacheKeyGenerator
    ) {}

    /**
     * Get query builder for articles.
     *
     * Provides a Spatie QueryBuilder instance for advanced filtering, sorting, and includes.
     *
     * @return QueryBuilder<Article> Query builder instance for Article model
     */
    public function query(): QueryBuilder
    {
        return $this->articleRepository->query();
    }

    /**
     * Load article relationships.
     *
     * Eagerly loads the specified relationships (e.g., author) for the article.
     *
     * @param  Article  $article  The article instance to load relationships for
     * @return Article The article with loaded relationships
     */
    public function loadArticleRelationships(Article $article): Article
    {
        return $this->articleRepository->loadRelationships($article, ['author']);
    }

    /**
     * Create a new article.
     *
     * Delegates article creation to the repository using the provided DTO.
     *
     * @param  CreateArticleDTO  $dto  Data transfer object containing article creation data
     * @return Article The newly created article instance
     */
    public function createArticle(CreateArticleDTO $dto): Article
    {
        return $this->articleRepository->create($dto);
    }

    /**
     * Update an existing article.
     *
     * Automatically calculates reading time if content changes, generates excerpt if empty,
     * and generates unique slug if title changes. Creates version automatically.
     *
     * @param  Article  $article  The article instance to update
     * @param  array<string, mixed>  $data  The data to update the article with
     * @return Article The updated article instance
     */
    public function updateArticle(Article $article, array $data): Article
    {
        if (isset($data['content']) && is_string($data['content'])) {
            $data['reading_time'] = $this->calculateReadingTime($data['content']);
        }

        if (isset($data['content']) && is_string($data['content']) && empty($data['excerpt'])) {
            $data['excerpt'] = $this->generateExcerpt($data['content']);
        }

        if (isset($data['title']) && is_string($data['title']) && empty($data['slug'])) {
            $excludeId = is_string($article->id) ? $article->id : null;
            $data['slug'] = $this->generateUniqueSlug($data['title'], $excludeId);
        }

        return $this->articleRepository->update($article, $data);
    }

    /**
     * Delete an article.
     *
     * Performs soft delete on the article, moving it to trash.
     *
     * @param  Article  $article  The article instance to delete
     * @return bool True if deletion was successful
     */
    public function deleteArticle(Article $article): bool
    {
        return $this->articleRepository->delete($article);
    }

    /**
     * Calculate reading time in minutes.
     *
     * Calculates estimated reading time based on 200 words per minute, minimum 1 minute.
     *
     * @param  string  $content  The article content
     * @return int Estimated reading time in minutes
     */
    private function calculateReadingTime(string $content): int
    {
        $wordCount = str_word_count(strip_tags($content));

        return max(1, (int) ceil($wordCount / 200));
    }

    /**
     * Generate excerpt from content.
     *
     * Strips HTML tags and truncates the text to the specified length with ellipsis.
     *
     * @param  string  $content  The full article content
     * @param  int  $length  Maximum excerpt length in characters (default: 200)
     * @return string The generated excerpt
     */
    private function generateExcerpt(string $content, int $length = 200): string
    {
        $text = strip_tags($content);

        return strlen($text) > $length ? substr($text, 0, $length) . '...' : $text;
    }

    /**
     * Generate unique slug for article.
     *
     * Generates URL-friendly slug from title, adds numeric suffix if duplicate exists.
     *
     * @param  string  $title  The article title
     * @param  string|null  $excludeId  Optional article ID to exclude from uniqueness check (for updates)
     * @return string The unique slug
     */
    private function generateUniqueSlug(string $title, ?string $excludeId = null): string
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $counter = 1;

        while ($this->articleRepository->slugExists($slug, $excludeId)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Create a manual version of an article.
     *
     * Manually creates a snapshot version of the article's current state.
     *
     * @param  Article  $article  The article to create version for
     * @param  string|null  $reason  Optional reason for creating the version
     * @return ArticleVersion The created version instance
     */
    public function createArticleVersion(Article $article, ?string $reason = null): ArticleVersion
    {
        return $article->createVersion($reason);
    }

    /**
     * Get all versions of an article.
     *
     * Retrieves all historical versions of the article ordered by version number.
     *
     * @param  Article  $article  The article to get versions for
     * @return Collection<int, ArticleVersion> Collection of all article versions
     */
    public function getArticleVersions(Article $article): Collection
    {
        return $article->versions()->get();
    }

    /**
     * Get a specific version of an article.
     *
     * Retrieves a specific historical version by its version number.
     *
     * @param  Article  $article  The article to get version for
     * @param  int  $versionNumber  The version number to retrieve
     * @return ArticleVersion|null The version instance or null if not found
     */
    public function getArticleVersion(Article $article, int $versionNumber): ?ArticleVersion
    {
        return $article->getVersion($versionNumber);
    }

    /**
     * Restore an article to a specific version.
     *
     * Reverts the article to the state captured in the specified version.
     *
     * @param  Article  $article  The article to restore
     * @param  int  $versionNumber  The version number to restore to
     * @return bool True if restoration was successful
     */
    public function restoreArticleToVersion(Article $article, int $versionNumber): bool
    {
        return $article->restoreToVersion($versionNumber);
    }

    /**
     * Compare two versions of an article.
     *
     * Returns a diff showing changes between two versions of the article.
     *
     * @param  Article  $article  The article to compare versions for
     * @param  int  $versionA  The first version number to compare
     * @param  int  $versionB  The second version number to compare
     * @return array<string, array<string, mixed>> Diff array showing changes between versions
     */
    public function compareArticleVersions(Article $article, int $versionA, int $versionB): array
    {
        return $article->compareVersions($versionA, $versionB);
    }

    /**
     * Get the version count for an article.
     *
     * Returns the total number of versions created for the article.
     *
     * @param  Article  $article  The article to count versions for
     * @return int Total number of versions
     */
    public function getArticleVersionCount(Article $article): int
    {
        return $article->getVersionCount();
    }

    /**
     * Update an article without creating a version.
     *
     * Temporarily disables automatic versioning during the update operation.
     * Useful for bulk updates or view count increments.
     *
     * @param  Article  $article  The article instance to update
     * @param  array<string, mixed>  $data  The data to update the article with
     * @return Article The updated article instance
     */
    public function updateArticleWithoutVersioning(Article $article, array $data): Article
    {
        $article->disableVersioning();

        $updated = $this->updateArticle($article, $data);

        $article->enableVersioning();

        return $updated;
    }

    /**
     * Get popular articles with caching.
     *
     * Retrieves most viewed articles within specified time period, cached for 1 hour.
     *
     * @param  int  $limit  Maximum number of articles to return (default: 10)
     * @param  int  $days  Time period in days to consider (default: 30)
     * @return Collection<int, Article> Collection of popular articles
     */
    public function getPopularArticles(int $limit = 10, int $days = 30): Collection
    {
        $cacheKey = $this->cacheKeyGenerator->generate('popular_articles', [
            'limit' => $limit,
            'days' => $days,
        ]);

        /** @var Collection<int, Article> $articles */
        $articles = Cache::remember(
            $cacheKey,
            now()->addHours(1),
            fn (): Collection => $this->articleRepository->getPopularArticles($limit, $days)
        );

        return $articles;
    }
}
