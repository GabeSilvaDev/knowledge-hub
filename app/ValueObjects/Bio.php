<?php

namespace App\ValueObjects;

use InvalidArgumentException;
use Stringable;

/**
 * Bio Value Object.
 *
 * Immutable value object representing a user biography.
 * Optional field with length validation and XSS protection.
 */
final readonly class Bio implements Stringable
{
    public function __construct(
        private ?string $value
    ) {
        if ($value !== null) {
            $this->validate($value);
        }
    }

    /**
     * Create a new Bio instance.
     *
     * @param  string|null  $bio  The biography text to create from
     * @return self The new Bio instance
     *
     * @throws InvalidArgumentException If bio exceeds maximum length or contains prohibited content
     */
    public static function from(?string $bio): self
    {
        return new self($bio);
    }

    /**
     * Get the raw bio value.
     *
     * @return string|null The biography text or null if not set
     */
    public function getValue(): ?string
    {
        return $this->value;
    }

    /**
     * Count the number of words in the bio.
     *
     * @return int The word count, or 0 if bio is null
     */
    public function getWordCount(): int
    {
        return $this->value ? str_word_count($this->value) : 0;
    }

    /**
     * Count the number of characters in the bio.
     *
     * @return int The character count, or 0 if bio is null
     */
    public function getCharacterCount(): int
    {
        return $this->value ? strlen($this->value) : 0;
    }

    /**
     * Check if bio is empty.
     *
     * @return bool True if bio is empty or null, false otherwise
     */
    public function isEmpty(): bool
    {
        return in_array(trim($this->value ?? ''), ['', '0'], true);
    }

    /**
     * Compare this bio with another for equality.
     *
     * @param  Bio  $other  The bio to compare with
     * @return bool True if bios are equal, false otherwise
     */
    public function equals(Bio $other): bool
    {
        return $this->value === $other->value;
    }

    /**
     * Convert bio to its string representation.
     *
     * @return string The bio text or empty string if null
     */
    public function __toString(): string
    {
        return $this->value ?? '';
    }

    /**
     * Validate the bio value.
     *
     * @param  string  $bio  The bio to validate
     *
     * @throws InvalidArgumentException If bio is too long or contains prohibited content
     */
    private function validate(string $bio): void
    {
        if (strlen($bio) > 500) {
            throw new InvalidArgumentException('Bio cannot be longer than 500 characters');
        }

        if (preg_match('/<script|javascript:|on\w+=/i', $bio)) {
            throw new InvalidArgumentException('Bio contains prohibited content');
        }
    }
}
