<?php

namespace App\Services;

use App\Cache\RedisCacheKeyGenerator;
use App\Contracts\ArticleRepositoryInterface;
use App\DTOs\CreateArticleDTO;
use App\Helpers\SlugHelper;
use App\Models\Article;
use App\Models\ArticleVersion;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Spatie\QueryBuilder\QueryBuilder;

class ArticleService
{
    public function __construct(
        private readonly ArticleRepositoryInterface $articleRepository,
        private readonly RedisCacheKeyGenerator $cacheKeyGenerator
    ) {}

    /**
     * Get query builder for articles.
     *
     * @return QueryBuilder<Article>
     */
    public function query(): QueryBuilder
    {
        return $this->articleRepository->query();
    }

    /**
     * Create a new article.
     */
    public function createArticle(CreateArticleDTO $dto): Article
    {
        return $this->articleRepository->create($dto);
    }

    /**
     * Update an existing article.
     *
     * @param  array<string, mixed>  $data
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
            $data['slug'] = SlugHelper::generateUniqueSlug($data['title'], Article::class, $excludeId);
        }

        return $this->articleRepository->update($article, $data);
    }

    /**
     * Delete an article.
     */
    public function deleteArticle(Article $article): bool
    {
        return $this->articleRepository->delete($article);
    }

    /**
     * Calculate reading time in minutes.
     */
    private function calculateReadingTime(string $content): int
    {
        $wordCount = str_word_count(strip_tags($content));

        return max(1, (int) ceil($wordCount / 200));
    }

    /**
     * Generate excerpt from content.
     */
    private function generateExcerpt(string $content, int $length = 200): string
    {
        $text = strip_tags($content);

        return strlen($text) > $length ? substr($text, 0, $length) . '...' : $text;
    }

    /**
     * Create a manual version of an article.
     */
    public function createArticleVersion(Article $article, ?string $reason = null): \App\Models\ArticleVersion
    {
        return $article->createVersion($reason);
    }

    /**
     * Get all versions of an article.
     *
     * @return Collection<int, \App\Models\ArticleVersion>
     */
    public function getArticleVersions(Article $article): Collection
    {
        return $article->versions()->get();
    }

    /**
     * Get a specific version of an article.
     */
    public function getArticleVersion(Article $article, int $versionNumber): ?ArticleVersion
    {
        return $article->getVersion($versionNumber);
    }

    /**
     * Restore an article to a specific version.
     */
    public function restoreArticleToVersion(Article $article, int $versionNumber): bool
    {
        return $article->restoreToVersion($versionNumber);
    }

    /**
     * Compare two versions of an article.
     *
     * @return array<string, array<string, mixed>>
     */
    public function compareArticleVersions(Article $article, int $versionA, int $versionB): array
    {
        return $article->compareVersions($versionA, $versionB);
    }

    /**
     * Get the version count for an article.
     */
    public function getArticleVersionCount(Article $article): int
    {
        return $article->getVersionCount();
    }

    /**
     * Update an article without creating a version.
     *
     * @param  array<string, mixed>  $data
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
     * @return Collection<int, Article>
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
            fn (): \Illuminate\Database\Eloquent\Collection => $this->articleRepository->getPopularArticles($limit, $days)
        );

        return $articles;
    }
}
