<?php

namespace App\ValueObjects;

use InvalidArgumentException;
use Stringable;

final readonly class Email implements Stringable
{
    public function __construct(
        private string $value
    ) {
        $this->validate($value);
    }

    public static function from(string $email): self
    {
        return new self($email);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getDomain(): string
    {
        $atPosition = strpos($this->value, '@');

        return $atPosition !== false ? substr($this->value, $atPosition + 1) : '';
    }

    public function getLocalPart(): string
    {
        $atPosition = strpos($this->value, '@');

        return $atPosition !== false ? substr($this->value, 0, $atPosition) : '';
    }

    public function equals(Email $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

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
