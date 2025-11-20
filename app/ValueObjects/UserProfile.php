<?php

namespace App\ValueObjects;

/**
 * UserProfile Value Object.
 *
 * Composite value object aggregating user profile information.
 * Contains name, username, and optional biography.
 */
final readonly class UserProfile
{
    public function __construct(
        public Name $name,
        public Username $username,
        public ?Bio $bio = null,
    ) {}

    /**
     * Create a new UserProfile instance.
     *
     * @param  Name  $name  The user's full name
     * @param  Username  $username  The user's username
     * @param  Bio|null  $bio  The user's biography
     * @return self The new UserProfile instance
     */
    public static function create(Name $name, Username $username, ?Bio $bio = null): self
    {
        return new self($name, $username, $bio);
    }

    /**
     * Get the user's name.
     *
     * @return Name The name value object
     */
    public function getName(): Name
    {
        return $this->name;
    }

    /**
     * Get the user's username.
     *
     * @return Username The username value object
     */
    public function getUsername(): Username
    {
        return $this->username;
    }

    /**
     * Get the user's biography.
     *
     * @return Bio|null The bio value object or null if not set
     */
    public function getBio(): ?Bio
    {
        return $this->bio;
    }

    /**
     * Compare this profile with another for equality.
     *
     * @param  UserProfile  $other  The profile to compare with
     * @return bool True if all components are equal, false otherwise
     */
    public function equals(UserProfile $other): bool
    {
        return $this->name->equals($other->name) &&
               $this->username->equals($other->username) &&
               (! $this->bio instanceof Bio && ! $other->bio instanceof Bio ||
                $this->bio instanceof Bio && $other->bio instanceof Bio && $this->bio->equals($other->bio));
    }
}
