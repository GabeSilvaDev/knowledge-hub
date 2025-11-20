<?php

namespace App\DTOs;

use Illuminate\Support\Arr;

/**
 * Data Transfer Object for updating a user profile.
 *
 * Encapsulates the data required to update user information.
 */
final readonly class UpdateUserDTO
{
    public function __construct(
        public ?string $name = null,
        public ?string $username = null,
        public ?string $bio = null,
        public ?string $avatarUrl = null,
    ) {}

    /**
     * Create a DTO from an array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $name = Arr::get($data, 'name');
        $username = Arr::get($data, 'username');
        $bio = Arr::get($data, 'bio');
        $avatarUrl = Arr::get($data, 'avatar_url');

        return new self(
            name: is_string($name) ? $name : null,
            username: is_string($username) ? $username : null,
            bio: is_string($bio) ? $bio : null,
            avatarUrl: is_string($avatarUrl) ? $avatarUrl : null,
        );
    }

    /**
     * Convert the DTO to an array, filtering out null values.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'username' => $this->username,
            'bio' => $this->bio,
            'avatar_url' => $this->avatarUrl,
        ], fn ($value) => $value !== null);
    }
}
