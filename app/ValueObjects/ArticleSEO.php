<?php

namespace App\ValueObjects;

final readonly class ArticleSEO
{
    public function __construct(
        public ?Title $seo_title = null,
        public ?string $seo_description = null,
        public ?string $seo_keywords = null,
    ) {}

    public static function create(?Title $seo_title = null, ?string $seo_description = null, ?string $seo_keywords = null): self
    {
        return new self($seo_title, $seo_description, $seo_keywords);
    }

    public static function fromContent(ArticleContent $content): self
    {
        return new self(
            seo_title: $content->getTitle(),
            seo_description: $content->getExcerpt(160),
            seo_keywords: null
        );
    }

    public function getSeoTitle(): ?Title
    {
        return $this->seo_title;
    }

    public function getSeoDescription(): ?string
    {
        return $this->seo_description;
    }

    public function getSeoKeywords(): ?string
    {
        return $this->seo_keywords;
    }

    public function isEmpty(): bool
    {
        return ! $this->seo_title instanceof Title &&
               $this->seo_description === null &&
               $this->seo_keywords === null;
    }

    public function equals(ArticleSEO $other): bool
    {
        return (! $this->seo_title instanceof Title && ! $other->seo_title instanceof Title ||
                $this->seo_title instanceof Title && $other->seo_title instanceof Title && $this->seo_title->equals($other->seo_title)) &&
               $this->seo_description === $other->seo_description &&
               $this->seo_keywords === $other->seo_keywords;
    }
}
