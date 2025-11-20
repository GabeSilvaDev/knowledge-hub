<?php

namespace App\ValueObjects;

use InvalidArgumentException;
use Stringable;

/**
 * Username Value Object.
 *
 * Immutable value object representing a unique username.
 * Enforces username format with letters, numbers, underscores, and hyphens only.
 */
final readonly class Username implements Stringable
{
    public function __construct(
        private string $value
    ) {
        $this->validate($value);
    }

    /**
     * Create a new Username instance.
     *
     * @param  string  $username  The username to create from
     * @return self The new Username instance
     *
     * @throws InvalidArgumentException If username is invalid
     */
    public static function from(string $username): self
    {
        return new self($username);
    }

    /**
     * Get the raw username value.
     *
     * @return string The username
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Compare this username with another for equality.
     *
     * @param  Username  $other  The username to compare with
     * @return bool True if usernames are equal, false otherwise
     */
    public function equals(Username $other): bool
    {
        return $this->value === $other->value;
    }

    /**
     * Convert username to its string representation.
     *
     * @return string The username
     */
    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * Validate the username value.
     *
     * @param  string  $username  The username to validate
     *
     * @throws InvalidArgumentException If username is empty, too short, too long, or has invalid format
     */
    private function validate(string $username): void
    {
        if ($username === '' || $username === '0') {
            throw new InvalidArgumentException('Username cannot be empty');
        }

        if (strlen($username) < 3) {
            throw new InvalidArgumentException('Username must be at least 3 characters long');
        }

        if (strlen($username) > 50) {
            throw new InvalidArgumentException('Username cannot be longer than 50 characters');
        }

        if (! preg_match('/^[a-zA-Z0-9_-]+$/', $username)) {
            throw new InvalidArgumentException('Username can only contain letters, numbers, underscores and hyphens');
        }

        if (preg_match('/(^[_-])|([_-]$)/', $username)) {
            throw new InvalidArgumentException('Username cannot start or end with underscore or hyphen');
        }
    }
}
