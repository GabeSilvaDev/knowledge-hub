<?php

namespace App\ValueObjects;

use InvalidArgumentException;
use Stringable;

/**
 * Content Value Object.
 *
 * Immutable value object representing article content.
 * Provides methods for content manipulation, word counting, and excerpt generation.
 */
final readonly class Content implements Stringable
{
    public function __construct(
        private string $value
    ) {
        $this->validate($value);
    }

    /**
     * Create a new Content instance.
     *
     * @param  string  $content  The content text to create from
     * @return self The new Content instance
     *
     * @throws InvalidArgumentException If content is empty
     */
    public static function from(string $content): self
    {
        return new self($content);
    }

    /**
     * Get the raw content value (may include HTML).
     *
     * @return string The content text
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Get content with HTML tags stripped.
     *
     * @return string The plain text content
     */
    public function getPlainText(): string
    {
        return strip_tags($this->value);
    }

    /**
     * Count the number of words in the content.
     *
     * @return int The word count
     */
    public function getWordCount(): int
    {
        return str_word_count($this->getPlainText());
    }

    /**
     * Count the number of characters in the content.
     *
     * @return int The character count
     */
    public function getCharacterCount(): int
    {
        return strlen($this->getPlainText());
    }

    /**
     * Generate an excerpt from the content.
     *
     * @param  int  $length  The maximum length of the excerpt
     * @return string The excerpt with ellipsis if truncated
     */
    public function getExcerpt(int $length = 200): string
    {
        $text = $this->getPlainText();

        return strlen($text) > $length ? substr($text, 0, $length) . '...' : $text;
    }

    /**
     * Calculate reading time based on word count.
     *
     * @param  int  $wordsPerMinute  The average reading speed
     * @return int The estimated reading time in minutes
     */
    public function getReadingTime(int $wordsPerMinute = 200): int
    {
        return max(1, (int) ceil($this->getWordCount() / $wordsPerMinute));
    }

    /**
     * Check if content is empty.
     *
     * @return bool True if content is empty, false otherwise
     */
    public function isEmpty(): bool
    {
        return in_array(trim($this->getPlainText()), ['', '0'], true);
    }

    /**
     * Compare this content with another for equality.
     *
     * @param  Content  $other  The content to compare with
     * @return bool True if contents are equal, false otherwise
     */
    public function equals(Content $other): bool
    {
        return $this->value === $other->value;
    }

    /**
     * Convert content to its string representation.
     *
     * @return string The content text
     */
    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * Validate the content value.
     *
     * @param  string  $content  The content to validate
     *
     * @throws InvalidArgumentException If content is empty
     */
    private function validate(string $content): void
    {
        if (in_array(trim($content), ['', '0'], true)) {
            throw new InvalidArgumentException('Content cannot be empty');
        }

        if (strlen($content) > 1000000) {
            throw new InvalidArgumentException('Content is too long');
        }
    }
}
