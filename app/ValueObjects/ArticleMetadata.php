<?php

namespace App\ValueObjects;

use App\Enums\ArticleStatus;
use App\Enums\ArticleType;
use DateTime;

/**
 * ArticleMetadata Value Object.
 *
 * Composite value object containing article metadata like status, type, and publication info.
 * Provides factory methods for common metadata configurations.
 */
final readonly class ArticleMetadata
{
    public function __construct(
        public ArticleStatus $status = ArticleStatus::DRAFT,
        public ArticleType $type = ArticleType::ARTICLE,
        public bool $is_featured = false,
        public bool $is_pinned = false,
        public ?DateTime $published_at = null,
    ) {}

    /**
     * Create a new ArticleMetadata instance with custom values.
     *
     * @param  ArticleStatus  $status  The article status
     * @param  ArticleType  $type  The article type
     * @param  bool  $is_featured  Whether article is featured
     * @param  bool  $is_pinned  Whether article is pinned
     * @param  DateTime|null  $published_at  The publication datetime
     * @return self The new ArticleMetadata instance
     */
    public static function create(
        ArticleStatus $status = ArticleStatus::DRAFT,
        ArticleType $type = ArticleType::ARTICLE,
        bool $is_featured = false,
        bool $is_pinned = false,
        ?DateTime $published_at = null
    ): self {
        return new self($status, $type, $is_featured, $is_pinned, $published_at);
    }

    /**
     * Create published article metadata.
     *
     * @param  ArticleType  $type  The article type
     * @return self The new ArticleMetadata instance with published status
     */
    public static function published(ArticleType $type = ArticleType::ARTICLE): self
    {
        return new self(
            status: ArticleStatus::PUBLISHED,
            type: $type,
            published_at: new DateTime
        );
    }

    /**
     * Create draft article metadata.
     *
     * @param  ArticleType  $type  The article type
     * @return self The new ArticleMetadata instance with draft status
     */
    public static function draft(ArticleType $type = ArticleType::ARTICLE): self
    {
        return new self(
            status: ArticleStatus::DRAFT,
            type: $type
        );
    }

    /**
     * Create featured article metadata.
     *
     * @param  ArticleType  $type  The article type
     * @return self The new ArticleMetadata instance with featured flag
     */
    public static function featured(ArticleType $type = ArticleType::ARTICLE): self
    {
        return new self(
            status: ArticleStatus::PUBLISHED,
            type: $type,
            is_featured: true,
            published_at: new DateTime
        );
    }

    /**
     * Get the article status.
     *
     * @return ArticleStatus The status enum
     */
    public function getStatus(): ArticleStatus
    {
        return $this->status;
    }

    /**
     * Get the article type.
     *
     * @return ArticleType The type enum
     */
    public function getType(): ArticleType
    {
        return $this->type;
    }

    /**
     * Check if article is featured.
     *
     * @return bool True if featured, false otherwise
     */
    public function isFeatured(): bool
    {
        return $this->is_featured;
    }

    /**
     * Check if article is pinned.
     *
     * @return bool True if pinned, false otherwise
     */
    public function isPinned(): bool
    {
        return $this->is_pinned;
    }

    /**
     * Get the publication datetime.
     *
     * @return DateTime|null The publication date or null if unpublished
     */
    public function getPublishedAt(): ?DateTime
    {
        return $this->published_at;
    }

    /**
     * Check if article is published and publication date has passed.
     *
     * @return bool True if published and date is in the past, false otherwise
     */
    public function isPublished(): bool
    {
        return $this->status === ArticleStatus::PUBLISHED &&
               $this->published_at instanceof DateTime &&
               $this->published_at <= new DateTime;
    }

    /**
     * Compare this metadata with another for equality.
     *
     * @param  ArticleMetadata  $other  The metadata to compare with
     * @return bool True if all fields are equal, false otherwise
     */
    public function equals(ArticleMetadata $other): bool
    {
        return $this->status === $other->status &&
               $this->type === $other->type &&
               $this->is_featured === $other->is_featured &&
               $this->is_pinned === $other->is_pinned &&
               $this->published_at == $other->published_at;
    }
}
