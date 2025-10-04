<?php

namespace App\ValueObjects;

use InvalidArgumentException;
use Stringable;

final readonly class Bio implements Stringable
{
    public function __construct(
        private ?string $value
    ) {
        if ($value !== null) {
            $this->validate($value);
        }
    }

    public static function from(?string $bio): self
    {
        return new self($bio);
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function getWordCount(): int
    {
        return $this->value ? str_word_count($this->value) : 0;
    }

    public function getCharacterCount(): int
    {
        return $this->value ? strlen($this->value) : 0;
    }

    public function isEmpty(): bool
    {
        return in_array(trim($this->value ?? ''), ['', '0'], true);
    }

    public function equals(Bio $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value ?? '';
    }

    private function validate(string $bio): void
    {
        if (strlen($bio) > 500) {
            throw new InvalidArgumentException('Bio cannot be longer than 500 characters');
        }

        if (preg_match('/<script|javascript:|on\w+=/i', $bio)) {
            throw new InvalidArgumentException('Bio contains prohibited content');
        }
    }
}
