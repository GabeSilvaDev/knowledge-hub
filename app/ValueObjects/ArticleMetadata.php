<?php

namespace App\ValueObjects;

use App\Enums\ArticleStatus;
use App\Enums\ArticleType;
use DateTime;

final readonly class ArticleMetadata
{
    public function __construct(
        public ArticleStatus $status = ArticleStatus::DRAFT,
        public ArticleType $type = ArticleType::ARTICLE,
        public bool $is_featured = false,
        public bool $is_pinned = false,
        public ?DateTime $published_at = null,
    ) {}

    public static function create(
        ArticleStatus $status = ArticleStatus::DRAFT,
        ArticleType $type = ArticleType::ARTICLE,
        bool $is_featured = false,
        bool $is_pinned = false,
        ?DateTime $published_at = null
    ): self {
        return new self($status, $type, $is_featured, $is_pinned, $published_at);
    }

    public static function published(ArticleType $type = ArticleType::ARTICLE): self
    {
        return new self(
            status: ArticleStatus::PUBLISHED,
            type: $type,
            published_at: new DateTime
        );
    }

    public static function draft(ArticleType $type = ArticleType::ARTICLE): self
    {
        return new self(
            status: ArticleStatus::DRAFT,
            type: $type
        );
    }

    public static function featured(ArticleType $type = ArticleType::ARTICLE): self
    {
        return new self(
            status: ArticleStatus::PUBLISHED,
            type: $type,
            is_featured: true,
            published_at: new DateTime
        );
    }

    public function getStatus(): ArticleStatus
    {
        return $this->status;
    }

    public function getType(): ArticleType
    {
        return $this->type;
    }

    public function isFeatured(): bool
    {
        return $this->is_featured;
    }

    public function isPinned(): bool
    {
        return $this->is_pinned;
    }

    public function getPublishedAt(): ?DateTime
    {
        return $this->published_at;
    }

    public function isPublished(): bool
    {
        return $this->status === ArticleStatus::PUBLISHED &&
               $this->published_at instanceof DateTime &&
               $this->published_at <= new DateTime;
    }

    public function equals(ArticleMetadata $other): bool
    {
        return $this->status === $other->status &&
               $this->type === $other->type &&
               $this->is_featured === $other->is_featured &&
               $this->is_pinned === $other->is_pinned &&
               $this->published_at == $other->published_at;
    }
}
