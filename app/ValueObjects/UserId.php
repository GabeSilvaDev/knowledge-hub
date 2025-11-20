<?php

namespace App\ValueObjects;

use InvalidArgumentException;
use Stringable;

/**
 * UserId Value Object.
 *
 * Immutable value object representing a MongoDB ObjectId.
 * Enforces 24-character hexadecimal format for MongoDB compatibility.
 */
final readonly class UserId implements Stringable
{
    public function __construct(
        private string $value
    ) {
        $this->validate($value);
    }

    /**
     * Create a new UserId instance from an existing ID.
     *
     * @param  string  $id  The MongoDB ObjectId string
     * @return self The new UserId instance
     *
     * @throws InvalidArgumentException If ID format is invalid
     */
    public static function from(string $id): self
    {
        return new self($id);
    }

    /**
     * Generate a new random UserId.
     *
     * @return self The new UserId instance with random value
     */
    public static function generate(): self
    {
        return new self(bin2hex(random_bytes(12)));
    }

    /**
     * Get the raw ID value.
     *
     * @return string The user ID
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Compare this user ID with another for equality.
     *
     * @param  UserId  $other  The user ID to compare with
     * @return bool True if IDs are equal, false otherwise
     */
    public function equals(UserId $other): bool
    {
        return $this->value === $other->value;
    }

    /**
     * Convert user ID to its string representation.
     *
     * @return string The user ID
     */
    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * Validate the user ID format.
     *
     * @param  string  $id  The ID to validate
     *
     * @throws InvalidArgumentException If ID is empty or not a valid 24-character hex string
     */
    private function validate(string $id): void
    {
        if ($id === '' || $id === '0') {
            throw new InvalidArgumentException('User ID cannot be empty');
        }

        if (! preg_match('/^[a-f\d]{24}$/i', $id)) {
            throw new InvalidArgumentException('Invalid User ID format');
        }
    }
}
