<?php

namespace App\ValueObjects;

/**
 * ArticleContent Value Object.
 *
 * Composite value object aggregating article title, content, and slug.
 * Provides convenience methods for generating excerpts and reading time.
 */
final readonly class ArticleContent
{
    public function __construct(
        public Title $title,
        public Content $content,
        public ?Slug $slug = null,
    ) {}

    /**
     * Create a new ArticleContent instance.
     *
     * @param  Title  $title  The article title
     * @param  Content  $content  The article content
     * @param  Slug|null  $slug  The article slug (optional)
     * @return self The new ArticleContent instance
     */
    public static function create(Title $title, Content $content, ?Slug $slug = null): self
    {
        return new self($title, $content, $slug);
    }

    /**
     * Get the article title.
     *
     * @return Title The title value object
     */
    public function getTitle(): Title
    {
        return $this->title;
    }

    /**
     * Get the article content.
     *
     * @return Content The content value object
     */
    public function getContent(): Content
    {
        return $this->content;
    }

    /**
     * Get the article slug.
     *
     * @return Slug|null The slug value object or null if not set
     */
    public function getSlug(): ?Slug
    {
        return $this->slug;
    }

    /**
     * Get the slug or generate one from the title.
     *
     * @return Slug The slug value object
     */
    public function getGeneratedSlug(): Slug
    {
        return $this->slug ?? Slug::fromTitle($this->title->getValue());
    }

    /**
     * Generate an excerpt from the content.
     *
     * @param  int  $length  The maximum length of the excerpt
     * @return string The excerpt text
     */
    public function getExcerpt(int $length = 200): string
    {
        return $this->content->getExcerpt($length);
    }

    /**
     * Calculate reading time for the content.
     *
     * @return int The reading time in minutes
     */
    public function getReadingTime(): int
    {
        return $this->content->getReadingTime();
    }

    /**
     * Compare this article content with another for equality.
     *
     * @param  ArticleContent  $other  The article content to compare with
     * @return bool True if all components are equal, false otherwise
     */
    public function equals(ArticleContent $other): bool
    {
        return $this->title->equals($other->title) &&
               $this->content->equals($other->content) &&
               (! $this->slug instanceof Slug && ! $other->slug instanceof Slug ||
                $this->slug instanceof Slug && $other->slug instanceof Slug && $this->slug->equals($other->slug));
    }
}
