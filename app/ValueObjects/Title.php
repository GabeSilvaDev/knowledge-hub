<?php

namespace App\ValueObjects;

use InvalidArgumentException;
use Stringable;

/**
 * Title Value Object.
 *
 * Immutable value object representing an article or content title.
 * Enforces title validation and provides utility methods for title manipulation.
 */
final readonly class Title implements Stringable
{
    public function __construct(
        private string $value
    ) {
        $this->validate($value);
    }

    /**
     * Create a new Title instance.
     *
     * @param  string  $title  The title text to create from
     * @return self The new Title instance
     *
     * @throws InvalidArgumentException If title is empty, too short, or too long
     */
    public static function from(string $title): self
    {
        return new self($title);
    }

    /**
     * Get the raw title value.
     *
     * @return string The title text
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Get the trimmed title value.
     *
     * @return string The title with whitespace removed from both ends
     */
    public function getTrimmed(): string
    {
        return trim($this->value);
    }

    /**
     * Count the number of words in the title.
     *
     * @return int The word count
     */
    public function getWordCount(): int
    {
        return str_word_count($this->value);
    }

    /**
     * Compare this title with another for equality.
     *
     * @param  Title  $other  The title to compare with
     * @return bool True if titles are equal, false otherwise
     */
    public function equals(Title $other): bool
    {
        return $this->value === $other->value;
    }

    /**
     * Convert the title to its string representation.
     *
     * @return string The title text
     */
    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * Validate the title value.
     *
     * @param  string  $title  The title to validate
     *
     * @throws InvalidArgumentException If title is empty, too short, or too long
     */
    private function validate(string $title): void
    {
        if (in_array(trim($title), ['', '0'], true)) {
            throw new InvalidArgumentException('Title cannot be empty');
        }

        if (strlen($title) < 3) {
            throw new InvalidArgumentException('Title must be at least 3 characters long');
        }

        if (strlen($title) > 255) {
            throw new InvalidArgumentException('Title cannot be longer than 255 characters');
        }

        if (preg_match('/[<>]/', $title)) {
            throw new InvalidArgumentException('Title cannot contain HTML tags');
        }
    }
}
