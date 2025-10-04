<?php

namespace App\ValueObjects;

use InvalidArgumentException;
use Stringable;

final readonly class Content implements Stringable
{
    public function __construct(
        private string $value
    ) {
        $this->validate($value);
    }

    public static function from(string $content): self
    {
        return new self($content);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getPlainText(): string
    {
        return strip_tags($this->value);
    }

    public function getWordCount(): int
    {
        return str_word_count($this->getPlainText());
    }

    public function getCharacterCount(): int
    {
        return strlen($this->getPlainText());
    }

    public function getExcerpt(int $length = 200): string
    {
        $text = $this->getPlainText();

        return strlen($text) > $length ? substr($text, 0, $length) . '...' : $text;
    }

    public function getReadingTime(int $wordsPerMinute = 200): int
    {
        return max(1, ceil($this->getWordCount() / $wordsPerMinute));
    }

    public function isEmpty(): bool
    {
        return in_array(trim($this->getPlainText()), ['', '0'], true);
    }

    public function equals(Content $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    private function validate(string $content): void
    {
        if (in_array(trim($content), ['', '0'], true)) {
            throw new InvalidArgumentException('Content cannot be empty');
        }

        if (strlen($content) > 1000000) {
            throw new InvalidArgumentException('Content is too long');
        }
    }
}
