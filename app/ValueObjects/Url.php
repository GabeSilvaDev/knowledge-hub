<?php

namespace App\ValueObjects;

use InvalidArgumentException;

final readonly class Url implements \Stringable
{
    public function __construct(
        private string $value
    ) {
        $this->validate($value);
    }

    public static function from(string $url): self
    {
        return new self($url);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getDomain(): string
    {
        return parse_url($this->value, PHP_URL_HOST) ?? '';
    }

    public function getScheme(): string
    {
        return parse_url($this->value, PHP_URL_SCHEME) ?? '';
    }

    public function isSecure(): bool
    {
        return $this->getScheme() === 'https';
    }

    public function equals(Url $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    private function validate(string $url): void
    {
        if ($url === '' || $url === '0') {
            throw new InvalidArgumentException('URL cannot be empty');
        }

        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException("Invalid URL format: {$url}");
        }

        if (strlen($url) > 2048) {
            throw new InvalidArgumentException('URL is too long');
        }

        $scheme = parse_url($url, PHP_URL_SCHEME);
        if (! in_array($scheme, ['http', 'https'])) {
            throw new InvalidArgumentException('URL must use HTTP or HTTPS protocol');
        }
    }
}
