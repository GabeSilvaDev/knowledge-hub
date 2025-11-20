<?php

namespace App\ValueObjects;

use InvalidArgumentException;
use Stringable;

/**
 * Name Value Object.
 *
 * Immutable value object representing a person's full name.
 * Provides methods for extracting first name, last name, and initials.
 */
final readonly class Name implements Stringable
{
    public function __construct(
        private string $value
    ) {
        $this->validate($value);
    }

    /**
     * Create a new Name instance.
     *
     * @param  string  $name  The full name to create from
     * @return self The new Name instance
     *
     * @throws InvalidArgumentException If name is invalid
     */
    public static function from(string $name): self
    {
        return new self($name);
    }

    /**
     * Get the raw name value.
     *
     * @return string The full name
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Get the first name from the full name.
     *
     * @return string The first name
     */
    public function getFirstName(): string
    {
        $parts = explode(' ', trim($this->value));

        return $parts[0];
    }

    /**
     * Get the last name from the full name.
     *
     * @return string The last name, or empty string if only one name
     */
    public function getLastName(): string
    {
        $parts = explode(' ', trim($this->value));

        return count($parts) > 1 ? end($parts) : '';
    }

    /**
     * Get the initials from the full name.
     *
     * @return string The initials in uppercase
     */
    public function getInitials(): string
    {
        $words = explode(' ', trim($this->value));
        $initials = '';

        foreach ($words as $word) {
            if ($word !== '' && $word !== '0') {
                $initials .= strtoupper($word[0]);
            }
        }

        return $initials;
    }

    /**
     * Compare this name with another for equality.
     *
     * @param  Name  $other  The name to compare with
     * @return bool True if names are equal, false otherwise
     */
    public function equals(Name $other): bool
    {
        return $this->value === $other->value;
    }

    /**
     * Convert name to its string representation.
     *
     * @return string The full name
     */
    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * Validate the name value.
     *
     * @param  string  $name  The name to validate
     *
     * @throws InvalidArgumentException If name is empty, too short, too long, or contains invalid characters
     */
    private function validate(string $name): void
    {
        if (in_array(trim($name), ['', '0'], true)) {
            throw new InvalidArgumentException('Name cannot be empty');
        }

        if (strlen($name) < 2) {
            throw new InvalidArgumentException('Name must be at least 2 characters long');
        }

        if (strlen($name) > 100) {
            throw new InvalidArgumentException('Name cannot be longer than 100 characters');
        }

        if (! preg_match('/^[a-zA-ZÀ-ÿ\s\'-]+$/', $name)) {
            throw new InvalidArgumentException('Name can only contain letters, spaces, apostrophes and hyphens');
        }
    }
}
