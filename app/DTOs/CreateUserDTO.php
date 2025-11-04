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
use InvalidArgumentException;

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
        $name = $data['name'] ?? '';
        $username = $data['username'] ?? '';
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';
        $bio = $data['bio'] ?? null;
        $avatarUrl = $data['avatar_url'] ?? null;
        $roles = $data['roles'] ?? [UserRole::READER];

        if (! is_string($name) || ! is_string($username) || ! is_string($email) || ! is_string($password)) {
            throw new InvalidArgumentException('Name, username, email and password must be strings');
        }

        if ($bio !== null && ! is_string($bio)) {
            throw new InvalidArgumentException('Bio must be a string or null');
        }

        if ($avatarUrl !== null && ! is_string($avatarUrl)) {
            throw new InvalidArgumentException('Avatar URL must be a string or null');
        }

        if (! is_array($roles)) {
            throw new InvalidArgumentException('Roles must be an array');
        }

        /** @var array<UserRole> $validatedRoles */
        $validatedRoles = [];
        foreach ($roles as $role) {
            if ($role instanceof UserRole) {
                $validatedRoles[] = $role;
            } elseif (is_string($role)) {
                $validatedRoles[] = UserRole::from($role);
            } else {
                throw new InvalidArgumentException('Each role must be a UserRole enum or valid string');
            }
        }

        if (empty($validatedRoles)) {
            $validatedRoles = [UserRole::READER];
        }

        return new self(
            profile: UserProfile::create(
                name: Name::from($name),
                username: Username::from($username),
                bio: Bio::from($bio)
            ),
            credentials: UserCredentials::create(
                email: Email::from($email),
                password: Password::fromPlainText($password),
                roles: $validatedRoles
            ),
            avatar_url: $avatarUrl !== null ? Url::from($avatarUrl) : null,
        );
    }
}
