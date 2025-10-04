<?php

namespace App\ValueObjects;

use App\Enums\UserRole;

final readonly class UserCredentials
{
    public function __construct(
        public Email $email,
        public Password $password,
        /** @var array<UserRole> */
        public array $roles = []
    ) {}

    /**
     * @param  array<UserRole>  $roles
     */
    public static function create(Email $email, Password $password, array $roles = []): self
    {
        return new self($email, $password, $roles);
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function getPassword(): Password
    {
        return $this->password;
    }

    /**
     * @return array<UserRole>
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    public function hasRole(UserRole $role): bool
    {
        return in_array($role, $this->roles);
    }

    public function equals(UserCredentials $other): bool
    {
        return $this->email->equals($other->email) &&
               $this->password->equals($other->password) &&
               $this->roles === $other->roles;
    }
}
