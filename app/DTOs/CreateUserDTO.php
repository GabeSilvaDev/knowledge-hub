<?php

namespace App\DTOs;

use App\Enums\UserRole;
use App\ValueObjects\Bio;
use App\ValueObjects\Email;
use App\ValueObjects\Name;
use App\ValueObjects\Password;
use App\ValueObjects\Url;
use App\ValueObjects\UserCredentials;
use App\ValueObjects\Username;
use App\ValueObjects\UserProfile;

class CreateUserDTO
{
    public function __construct(
        public readonly UserProfile $profile,
        public readonly UserCredentials $credentials,
        public readonly ?Url $avatar_url = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->profile->getName()->getValue(),
            'email' => $this->credentials->getEmail()->getValue(),
            'password' => $this->credentials->getPassword()->getHashedValue(),
            'username' => $this->profile->getUsername()->getValue(),
            'avatar_url' => $this->avatar_url?->getValue(),
            'bio' => $this->profile->getBio()?->getValue(),
            'roles' => array_map(fn (UserRole $role) => $role->value, $this->credentials->getRoles()),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            profile: UserProfile::create(
                name: Name::from($data['name']),
                username: Username::from($data['username']),
                bio: Bio::from($data['bio'] ?? null)
            ),
            credentials: UserCredentials::create(
                email: Email::from($data['email']),
                password: Password::fromPlainText($data['password']),
                roles: $data['roles'] ?? [UserRole::READER]
            ),
            avatar_url: isset($data['avatar_url']) ? Url::from($data['avatar_url']) : null,
        );
    }
}
