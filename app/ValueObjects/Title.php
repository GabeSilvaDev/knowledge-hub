<?php

namespace App\ValueObjects;

use InvalidArgumentException;
use Stringable;

final readonly class Title implements Stringable
{
    public function __construct(
        private string $value
    ) {
        $this->validate($value);
    }

    public static function from(string $title): self
    {
        return new self($title);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getTrimmed(): string
    {
        return trim($this->value);
    }

    public function getWordCount(): int
    {
        return str_word_count($this->value);
    }

    public function equals(Title $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    private function validate(string $title): void
    {
        if (in_array(trim($title), ['', '0'], true)) {
            throw new InvalidArgumentException('Title cannot be empty');
        }

        if (strlen($title) < 3) {
            throw new InvalidArgumentException('Title must be at least 3 characters long');
        }

        if (strlen($title) > 255) {
            throw new InvalidArgumentException('Title cannot be longer than 255 characters');
        }

        if (preg_match('/[<>]/', $title)) {
            throw new InvalidArgumentException('Title cannot contain HTML tags');
        }
    }
}
