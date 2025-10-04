<?php

namespace App\ValueObjects;

final readonly class UserProfile
{
    public function __construct(
        public Name $name,
        public Username $username,
        public ?Bio $bio = null,
    ) {}

    public static function create(Name $name, Username $username, ?Bio $bio = null): self
    {
        return new self($name, $username, $bio);
    }

    public function getName(): Name
    {
        return $this->name;
    }

    public function getUsername(): Username
    {
        return $this->username;
    }

    public function getBio(): ?Bio
    {
        return $this->bio;
    }

    public function equals(UserProfile $other): bool
    {
        return $this->name->equals($other->name) &&
               $this->username->equals($other->username) &&
               (! $this->bio instanceof Bio && ! $other->bio instanceof Bio ||
                $this->bio instanceof Bio && $other->bio instanceof Bio && $this->bio->equals($other->bio));
    }
}
