<?php

namespace App\Services;

use App\Contracts\ArticleRepositoryInterface;
use App\DTOs\CreateArticleDTO;
use App\Helpers\SlugHelper;
use App\Models\Article;
use Spatie\QueryBuilder\QueryBuilder;

class ArticleService
{
    public function __construct(
        private readonly ArticleRepositoryInterface $articleRepository
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
}
