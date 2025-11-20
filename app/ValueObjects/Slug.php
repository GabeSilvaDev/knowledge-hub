<?php

namespace App\ValueObjects;

use InvalidArgumentException;
use Stringable;

/**
 * Slug Value Object.
 *
 * Immutable value object representing a URL-friendly slug.
 * Enforces lowercase alphanumeric characters with hyphens only.
 */
final readonly class Slug implements Stringable
{
    public function __construct(
        private string $value
    ) {
        $this->validate($value);
    }

    /**
     * Create a new Slug instance from a slug string.
     *
     * @param  string  $slug  The slug to create from
     * @return self The new Slug instance
     *
     * @throws InvalidArgumentException If slug is invalid
     */
    public static function from(string $slug): self
    {
        return new self($slug);
    }

    /**
     * Create a new Slug instance from a title string.
     *
     * Converts title to lowercase, removes special characters, and replaces spaces with hyphens.
     *
     * @param  string  $title  The title to convert to slug
     * @return self The new Slug instance
     */
    public static function fromTitle(string $title): self
    {
        $slug = strtolower(trim($title));
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = preg_replace('/[\s-]+/', '-', (string) $slug);
        $slug = trim((string) $slug, '-');

        return new self($slug);
    }

    /**
     * Get the raw slug value.
     *
     * @return string The slug text
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Compare this slug with another for equality.
     *
     * @param  Slug  $other  The slug to compare with
     * @return bool True if slugs are equal, false otherwise
     */
    public function equals(Slug $other): bool
    {
        return $this->value === $other->value;
    }

    /**
     * Convert slug to its string representation.
     *
     * @return string The slug text
     */
    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * Validate the slug value.
     *
     * @param  string  $slug  The slug to validate
     *
     * @throws InvalidArgumentException If slug is empty, too long, contains invalid characters, or has invalid format
     */
    private function validate(string $slug): void
    {
        if ($slug === '' || $slug === '0') {
            throw new InvalidArgumentException('Slug cannot be empty');
        }

        if (strlen($slug) > 255) {
            throw new InvalidArgumentException('Slug cannot be longer than 255 characters');
        }

        if (! preg_match('/^[a-z0-9-]+$/', $slug)) {
            throw new InvalidArgumentException('Slug can only contain lowercase letters, numbers and hyphens');
        }

        if (preg_match('/(^-)|(-$)/', $slug)) {
            throw new InvalidArgumentException('Slug cannot start or end with hyphen');
        }

        if (str_contains($slug, '--')) {
            throw new InvalidArgumentException('Slug cannot contain consecutive hyphens');
        }
    }
}
