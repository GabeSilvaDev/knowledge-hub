<?php

namespace App\ValueObjects;

use InvalidArgumentException;
use Stringable;

final readonly class Name implements Stringable
{
    public function __construct(
        private string $value
    ) {
        $this->validate($value);
    }

    public static function from(string $name): self
    {
        return new self($name);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getFirstName(): string
    {
        $parts = explode(' ', trim($this->value));

        return $parts[0] ?? '';
    }

    public function getLastName(): string
    {
        $parts = explode(' ', trim($this->value));

        return count($parts) > 1 ? end($parts) : '';
    }

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

    public function equals(Name $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

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
