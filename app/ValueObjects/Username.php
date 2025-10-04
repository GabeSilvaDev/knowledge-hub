<?php

namespace App\ValueObjects;

use InvalidArgumentException;
use Stringable;

final readonly class Username implements Stringable
{
    public function __construct(
        private string $value
    ) {
        $this->validate($value);
    }

    public static function from(string $username): self
    {
        return new self($username);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function equals(Username $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

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
