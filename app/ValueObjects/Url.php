<?php

namespace App\ValueObjects;

use InvalidArgumentException;
use Stringable;

/**
 * Url Value Object.
 *
 * Immutable value object representing a validated HTTP/HTTPS URL.
 * Provides methods for extracting domain, scheme, and security checking.
 */
final readonly class Url implements Stringable
{
    public function __construct(
        private string $value
    ) {
        $this->validate($value);
    }

    /**
     * Create a new Url instance.
     *
     * @param  string  $url  The URL to create from
     * @return self The new Url instance
     *
     * @throws InvalidArgumentException If URL is invalid or uses unsupported protocol
     */
    public static function from(string $url): self
    {
        return new self($url);
    }

    /**
     * Get the raw URL value.
     *
     * @return string The URL
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Get the domain from the URL.
     *
     * @return string The domain name
     */
    public function getDomain(): string
    {
        $host = parse_url($this->value, PHP_URL_HOST);

        return is_string($host) ? $host : '';
    }

    /**
     * Get the scheme from the URL.
     *
     * @return string The scheme (http or https)
     */
    public function getScheme(): string
    {
        $scheme = parse_url($this->value, PHP_URL_SCHEME);

        return is_string($scheme) ? $scheme : '';
    }

    /**
     * Check if URL uses HTTPS protocol.
     *
     * @return bool True if URL is secure (HTTPS), false otherwise
     */
    public function isSecure(): bool
    {
        return $this->getScheme() === 'https';
    }

    /**
     * Compare this URL with another for equality.
     *
     * @param  Url  $other  The URL to compare with
     * @return bool True if URLs are equal, false otherwise
     */
    public function equals(Url $other): bool
    {
        return $this->value === $other->value;
    }

    /**
     * Convert URL to its string representation.
     *
     * @return string The URL
     */
    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * Validate the URL value.
     *
     * @param  string  $url  The URL to validate
     *
     * @throws InvalidArgumentException If URL is empty, invalid format, too long, or uses unsupported protocol
     */
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
