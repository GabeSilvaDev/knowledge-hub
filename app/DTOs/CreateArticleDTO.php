<?php

namespace App\DTOs;

use App\Enums\ArticleStatus;
use App\Enums\ArticleType;
use App\ValueObjects\ArticleContent;
use App\ValueObjects\ArticleMetadata;
use App\ValueObjects\ArticleSEO;
use App\ValueObjects\Content;
use App\ValueObjects\Slug;
use App\ValueObjects\Title;
use App\ValueObjects\Url;
use App\ValueObjects\UserId;
use DateTime;

class CreateArticleDTO
{
    public function __construct(
        public readonly ArticleContent $content,
        public readonly UserId $author_id,
        public readonly ArticleMetadata $metadata,
        public readonly ?Url $featured_image = null,
        /** @var array<string> */
        public readonly array $tags = [],
        /** @var array<string> */
        public readonly array $categories = [],
        /** @var array<string, mixed> */
        public readonly array $meta_data = [],
        public readonly ?ArticleSEO $seo = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'title' => $this->content->getTitle()->getValue(),
            'slug' => $this->content->getGeneratedSlug()->getValue(),
            'content' => $this->content->getContent()->getValue(),
            'excerpt' => $this->content->getExcerpt(),
            'author_id' => $this->author_id->getValue(),
            'status' => $this->metadata->getStatus()->value,
            'type' => $this->metadata->getType()->value,
            'featured_image' => $this->featured_image?->getValue(),
            'tags' => $this->tags,
            'categories' => $this->categories,
            'meta_data' => $this->meta_data,
            'view_count' => 0,
            'like_count' => 0,
            'comment_count' => 0,
            'reading_time' => $this->content->getReadingTime(),
            'is_featured' => $this->metadata->isFeatured(),
            'is_pinned' => $this->metadata->isPinned(),
            'published_at' => $this->metadata->getPublishedAt(),
            'seo_title' => $this->seo?->getSeoTitle()?->getValue(),
            'seo_description' => $this->seo?->getSeoDescription(),
            'seo_keywords' => $this->seo?->getSeoKeywords(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $seoTitle = isset($data['seo_title']) ? Title::from($data['seo_title']) : null;
        $hasSeo = $seoTitle instanceof Title || ! empty($data['seo_description']) || ! empty($data['seo_keywords']);
        $seo = $hasSeo
            ? ArticleSEO::create($seoTitle, $data['seo_description'] ?? null, $data['seo_keywords'] ?? null)
            : null;

        return new self(
            content: ArticleContent::create(
                title: Title::from($data['title']),
                content: Content::from($data['content']),
                slug: isset($data['slug']) ? Slug::from($data['slug']) : null
            ),
            author_id: UserId::from($data['author_id']),
            metadata: ArticleMetadata::create(
                status: ArticleStatus::from($data['status'] ?? ArticleStatus::DRAFT->value),
                type: ArticleType::from($data['type'] ?? ArticleType::ARTICLE->value),
                is_featured: $data['is_featured'] ?? false,
                is_pinned: $data['is_pinned'] ?? false,
                published_at: isset($data['published_at']) ? new DateTime($data['published_at']) : null
            ),
            featured_image: isset($data['featured_image']) ? Url::from($data['featured_image']) : null,
            tags: $data['tags'] ?? [],
            categories: $data['categories'] ?? [],
            meta_data: $data['meta_data'] ?? [],
            seo: $seo,
        );
    }
}
