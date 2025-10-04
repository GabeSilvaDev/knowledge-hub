<?php

namespace App\Services;

use App\Contracts\ArticleRepositoryInterface;
use App\DTOs\CreateArticleDTO;
use App\Enums\ArticleStatus;
use App\Enums\ArticleType;
use App\Helpers\SlugHelper;
use App\Models\Article;
use App\ValueObjects\ArticleContent;
use App\ValueObjects\ArticleMetadata;
use App\ValueObjects\ArticleSEO;
use App\ValueObjects\Content;
use App\ValueObjects\Slug;
use App\ValueObjects\Title;
use App\ValueObjects\Url;
use App\ValueObjects\UserId;
use DateTime;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ArticleService
{
    public function __construct(
        private readonly ArticleRepositoryInterface $articleRepository
    ) {}

    /**
     * Create a new article.
     */
    public function createArticle(CreateArticleDTO $dto): Article
    {
        $data = $dto->toArray();
        if (empty($data['slug'])) {
            $data['slug'] = SlugHelper::generateUniqueSlug($data['title'], Article::class);
        }

        $data['reading_time'] = $this->calculateReadingTime($data['content']);

        if (empty($data['excerpt'])) {
            $data['excerpt'] = $this->generateExcerpt($data['content']);
        }

        $content = ArticleContent::create(
            Title::from($data['title']),
            Content::from($data['content']),
            isset($data['slug']) ? Slug::from($data['slug']) : null
        );

        $metadata = ArticleMetadata::create(
            ArticleStatus::from($data['status']),
            ArticleType::from($data['type']),
            $data['is_featured'] ?? false,
            $data['is_pinned'] ?? false,
            isset($data['published_at']) ? new DateTime($data['published_at']) : null
        );

        $seo = null;
        if (! empty($data['seo_title']) || ! empty($data['seo_description']) || ! empty($data['seo_keywords'])) {
            $seo = ArticleSEO::create(
                isset($data['seo_title']) ? Title::from($data['seo_title']) : null,
                $data['seo_description'] ?? null,
                $data['seo_keywords'] ?? null
            );
        }

        return $this->articleRepository->create(new CreateArticleDTO(
            content: $content,
            author_id: UserId::from($data['author_id']),
            metadata: $metadata,
            featured_image: isset($data['featured_image']) ? Url::from($data['featured_image']) : null,
            tags: $data['tags'] ?? [],
            categories: $data['categories'] ?? [],
            meta_data: $data['meta_data'] ?? [],
            seo: $seo
        ));
    }

    /**
     * Get article by ID.
     */
    public function getArticleById(string $id): ?Article
    {
        return $this->articleRepository->findById($id);
    }

    /**
     * Get article by slug.
     */
    public function getArticleBySlug(string $slug): ?Article
    {
        return $this->articleRepository->findBySlug($slug);
    }

    /**
     * Get paginated articles.
     */
    public function getArticles(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->articleRepository->paginate($perPage, $filters);
    }

    /**
     * Get published articles.
     */
    public function getPublishedArticles(): Collection
    {
        return $this->articleRepository->getPublished();
    }

    /**
     * Get featured articles.
     */
    public function getFeaturedArticles(): Collection
    {
        return $this->articleRepository->getFeatured();
    }

    /**
     * Get articles by author.
     */
    public function getArticlesByAuthor(string $authorId): Collection
    {
        return $this->articleRepository->getByAuthor($authorId);
    }

    /**
     * Get articles by type.
     */
    public function getArticlesByType(ArticleType $type): Collection
    {
        return $this->articleRepository->getByType($type->value);
    }

    /**
     * Search articles.
     */
    public function searchArticles(string $term): Collection
    {
        return $this->articleRepository->search($term);
    }

    /**
     * Get articles by tags.
     */
    public function getArticlesByTags(array $tags): Collection
    {
        return $this->articleRepository->getByTags($tags);
    }

    /**
     * Calculate reading time in minutes.
     */
    private function calculateReadingTime(string $content): int
    {
        $wordCount = str_word_count(strip_tags($content));

        return max(1, ceil($wordCount / 200));
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
