<?php

namespace App\ValueObjects;

use InvalidArgumentException;
use Stringable;

final readonly class Slug implements Stringable
{
    public function __construct(
        private string $value
    ) {
        $this->validate($value);
    }

    public static function from(string $slug): self
    {
        return new self($slug);
    }

    public static function fromTitle(string $title): self
    {
        $slug = strtolower(trim($title));
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = preg_replace('/[\s-]+/', '-', (string) $slug);
        $slug = trim((string) $slug, '-');

        return new self($slug);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function equals(Slug $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

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
