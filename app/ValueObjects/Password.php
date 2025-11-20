<?php

namespace App\ValueObjects;

use Illuminate\Support\Facades\Hash;
use InvalidArgumentException;
use Stringable;

/**
 * Password Value Object.
 *
 * Immutable value object representing a hashed password.
 * Enforces password strength requirements and provides verification methods.
 */
final readonly class Password implements Stringable
{
    public function __construct(
        private string $hashedValue
    ) {}

    /**
     * Create a new Password instance from plain text.
     *
     * Validates and hashes the plain password before storing.
     *
     * @param  string  $plainPassword  The plain text password
     * @return self The new Password instance with hashed value
     *
     * @throws InvalidArgumentException If password is empty or too short
     */
    public static function fromPlainText(string $plainPassword): self
    {
        self::validatePlainText($plainPassword);

        return new self(Hash::make($plainPassword));
    }

    /**
     * Create a new Password instance from an already hashed password.
     *
     * @param  string  $hashedPassword  The hashed password
     * @return self The new Password instance
     *
     * @throws InvalidArgumentException If hashed password is empty
     */
    public static function fromHash(string $hashedPassword): self
    {
        if ($hashedPassword === '' || $hashedPassword === '0') {
            throw new InvalidArgumentException('Hashed password cannot be empty');
        }

        return new self($hashedPassword);
    }

    /**
     * Get the hashed password value.
     *
     * @return string The hashed password
     */
    public function getHashedValue(): string
    {
        return $this->hashedValue;
    }

    /**
     * Verify a plain password against the hashed password.
     *
     * @param  string  $plainPassword  The plain text password to verify
     * @return bool True if password matches, false otherwise
     */
    public function verify(string $plainPassword): bool
    {
        return Hash::check($plainPassword, $this->hashedValue);
    }

    /**
     * Compare this password with another for equality.
     *
     * @param  Password  $other  The password to compare with
     * @return bool True if passwords are equal, false otherwise
     */
    public function equals(Password $other): bool
    {
        return $this->hashedValue === $other->hashedValue;
    }

    /**
     * Convert password to its string representation.
     *
     * @return string The hashed password
     */
    public function __toString(): string
    {
        return $this->hashedValue;
    }

    /**
     * Validate plain text password.
     *
     * @param  string  $password  The password to validate
     *
     * @throws InvalidArgumentException If password is empty, too short, or too long
     */
    private static function validatePlainText(string $password): void
    {
        if ($password === '' || $password === '0') {
            throw new InvalidArgumentException('Password cannot be empty');
        }

        if (strlen($password) < 8) {
            throw new InvalidArgumentException('Password must be at least 8 characters long');
        }

        if (strlen($password) > 255) {
            throw new InvalidArgumentException('Password cannot be longer than 255 characters');
        }

        if (! preg_match('/[A-Z]/', $password)) {
            throw new InvalidArgumentException('Password must contain at least one uppercase letter');
        }

        if (! preg_match('/[a-z]/', $password)) {
            throw new InvalidArgumentException('Password must contain at least one lowercase letter');
        }

        if (! preg_match('/\d/', $password)) {
            throw new InvalidArgumentException('Password must contain at least one number');
        }
    }
}
