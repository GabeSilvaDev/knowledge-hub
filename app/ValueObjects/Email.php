<?php

namespace App\ValueObjects;

use InvalidArgumentException;
use Stringable;

/**
 * Email Value Object.
 *
 * Immutable value object representing a validated email address.
 * Enforces email format validation and provides domain/local part extraction.
 */
final readonly class Email implements Stringable
{
    public function __construct(
        private string $value
    ) {
        $this->validate($value);
    }

    /**
     * Create a new Email instance.
     *
     * @param  string  $email  The email address to create from
     * @return self The new Email instance
     *
     * @throws InvalidArgumentException If email is invalid or too long
     */
    public static function from(string $email): self
    {
        return new self($email);
    }

    /**
     * Get the raw email value.
     *
     * @return string The email address
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Get the domain part of the email address.
     *
     * @return string The domain (part after @)
     */
    public function getDomain(): string
    {
        $atPosition = strpos($this->value, '@');

        return $atPosition !== false ? substr($this->value, $atPosition + 1) : '';
    }

    /**
     * Get the local part of the email address.
     *
     * @return string The local part (part before @)
     */
    public function getLocalPart(): string
    {
        $atPosition = strpos($this->value, '@');

        return $atPosition !== false ? substr($this->value, 0, $atPosition) : '';
    }

    /**
     * Compare this email with another for equality.
     *
     * @param  Email  $other  The email to compare with
     * @return bool True if emails are equal, false otherwise
     */
    public function equals(Email $other): bool
    {
        return $this->value === $other->value;
    }

    /**
     * Convert email to its string representation.
     *
     * @return string The email address
     */
    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * Validate the email address.
     *
     * @param  string  $email  The email to validate
     *
     * @throws InvalidArgumentException If email is empty, too long, or invalid format
     */
    private function validate(string $email): void
    {
        if ($email === '' || $email === '0') {
            throw new InvalidArgumentException('Email cannot be empty');
        }

        if (strlen($email) > 254) {
            throw new InvalidArgumentException('Email is too long');
        }

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Invalid email format: {$email}");
        }
    }
}
