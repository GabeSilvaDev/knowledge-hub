<?php

namespace App\ValueObjects;

use Illuminate\Support\Facades\Hash;
use InvalidArgumentException;
use Stringable;

final readonly class Password implements Stringable
{
    public function __construct(
        private string $hashedValue
    ) {}

    public static function fromPlainText(string $plainPassword): self
    {
        self::validatePlainText($plainPassword);

        return new self(Hash::make($plainPassword));
    }

    public static function fromHash(string $hashedPassword): self
    {
        if ($hashedPassword === '' || $hashedPassword === '0') {
            throw new InvalidArgumentException('Hashed password cannot be empty');
        }

        return new self($hashedPassword);
    }

    public function getHashedValue(): string
    {
        return $this->hashedValue;
    }

    public function verify(string $plainPassword): bool
    {
        return Hash::check($plainPassword, $this->hashedValue);
    }

    public function equals(Password $other): bool
    {
        return $this->hashedValue === $other->hashedValue;
    }

    public function __toString(): string
    {
        return $this->hashedValue;
    }

    private static function validatePlainText(string $password): void
    {
        if ($password === '' || $password === '0') {
            throw new InvalidArgumentException('Password cannot be empty');
        }

        if (strlen($password) < 8) {
            throw new InvalidArgumentException('Password must be at least 8 characters long');
        }

        if (strlen($password) > 255) {
            throw new InvalidArgumentException('Password cannot be longer than 255 characters');
        }

        if (! preg_match('/[A-Z]/', $password)) {
            throw new InvalidArgumentException('Password must contain at least one uppercase letter');
        }

        if (! preg_match('/[a-z]/', $password)) {
            throw new InvalidArgumentException('Password must contain at least one lowercase letter');
        }

        if (! preg_match('/\d/', $password)) {
            throw new InvalidArgumentException('Password must contain at least one number');
        }
    }
}
