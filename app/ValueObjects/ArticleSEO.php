<?php

namespace App\ValueObjects;

/**
 * ArticleSEO Value Object.
 *
 * Composite value object containing SEO metadata for articles.
 * Provides factory method for generating SEO data from article content.
 */
final readonly class ArticleSEO
{
    public function __construct(
        public ?Title $seo_title = null,
        public ?string $seo_description = null,
        public ?string $seo_keywords = null,
    ) {}

    /**
     * Create a new ArticleSEO instance with custom values.
     *
     * @param  Title|null  $seo_title  The SEO title
     * @param  string|null  $seo_description  The SEO description
     * @param  string|null  $seo_keywords  The SEO keywords
     * @return self The new ArticleSEO instance
     */
    public static function create(?Title $seo_title = null, ?string $seo_description = null, ?string $seo_keywords = null): self
    {
        return new self($seo_title, $seo_description, $seo_keywords);
    }

    /**
     * Create ArticleSEO from article content.
     *
     * Automatically generates SEO title and description from content.
     *
     * @param  ArticleContent  $content  The article content
     * @return self The new ArticleSEO instance
     */
    public static function fromContent(ArticleContent $content): self
    {
        return new self(
            seo_title: $content->getTitle(),
            seo_description: $content->getExcerpt(160)
        );
    }

    /**
     * Get the SEO title.
     *
     * @return Title|null The SEO title or null if not set
     */
    public function getSeoTitle(): ?Title
    {
        return $this->seo_title;
    }

    /**
     * Get the SEO description.
     *
     * @return string|null The SEO description or null if not set
     */
    public function getSeoDescription(): ?string
    {
        return $this->seo_description;
    }

    /**
     * Get the SEO keywords.
     *
     * @return string|null The SEO keywords or null if not set
     */
    public function getSeoKeywords(): ?string
    {
        return $this->seo_keywords;
    }

    /**
     * Check if all SEO fields are empty.
     *
     * @return bool True if no SEO data is set, false otherwise
     */
    public function isEmpty(): bool
    {
        return ! $this->seo_title instanceof Title &&
               $this->seo_description === null &&
               $this->seo_keywords === null;
    }

    /**
     * Compare this SEO data with another for equality.
     *
     * @param  ArticleSEO  $other  The SEO data to compare with
     * @return bool True if all fields are equal, false otherwise
     */
    public function equals(ArticleSEO $other): bool
    {
        return (! $this->seo_title instanceof Title && ! $other->seo_title instanceof Title ||
                $this->seo_title instanceof Title && $other->seo_title instanceof Title && $this->seo_title->equals($other->seo_title)) &&
               $this->seo_description === $other->seo_description &&
               $this->seo_keywords === $other->seo_keywords;
    }
}
