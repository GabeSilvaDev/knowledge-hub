<?php

namespace App\ValueObjects;

use InvalidArgumentException;
use Stringable;

final readonly class UserId implements Stringable
{
    public function __construct(
        private string $value
    ) {
        $this->validate($value);
    }

    public static function from(string $id): self
    {
        return new self($id);
    }

    public static function generate(): self
    {
        return new self(bin2hex(random_bytes(12)));
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function equals(UserId $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    private function validate(string $id): void
    {
        if ($id === '' || $id === '0') {
            throw new InvalidArgumentException('User ID cannot be empty');
        }

        if (! preg_match('/^[a-f\d]{24}$/i', $id)) {
            throw new InvalidArgumentException('Invalid User ID format');
        }
    }
}
