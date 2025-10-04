<?php

namespace App\ValueObjects;

final readonly class ArticleContent
{
    public function __construct(
        public Title $title,
        public Content $content,
        public ?Slug $slug = null,
    ) {}

    public static function create(Title $title, Content $content, ?Slug $slug = null): self
    {
        return new self($title, $content, $slug);
    }

    public function getTitle(): Title
    {
        return $this->title;
    }

    public function getContent(): Content
    {
        return $this->content;
    }

    public function getSlug(): ?Slug
    {
        return $this->slug;
    }

    public function getGeneratedSlug(): Slug
    {
        return $this->slug ?? Slug::fromTitle($this->title->getValue());
    }

    public function getExcerpt(int $length = 200): string
    {
        return $this->content->getExcerpt($length);
    }

    public function getReadingTime(): int
    {
        return $this->content->getReadingTime();
    }

    public function equals(ArticleContent $other): bool
    {
        return $this->title->equals($other->title) &&
               $this->content->equals($other->content) &&
               (! $this->slug instanceof Slug && ! $other->slug instanceof Slug ||
                $this->slug instanceof Slug && $other->slug instanceof Slug && $this->slug->equals($other->slug));
    }
}
