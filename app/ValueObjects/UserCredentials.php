<?php

namespace App\ValueObjects;

use App\Enums\UserRole;

/**
 * UserCredentials Value Object.
 *
 * Composite value object aggregating user authentication credentials.
 * Contains email, password, and role assignments.
 */
final readonly class UserCredentials
{
    public function __construct(
        public Email $email,
        public Password $password,
        /** @var array<UserRole> */
        public array $roles = []
    ) {}

    /**
     * Create a new UserCredentials instance.
     *
     * @param  Email  $email  The user's email
     * @param  Password  $password  The user's password
     * @param  array<UserRole>  $roles  The user's roles
     * @return self The new UserCredentials instance
     */
    public static function create(Email $email, Password $password, array $roles = []): self
    {
        return new self($email, $password, $roles);
    }

    /**
     * Get the user's email.
     *
     * @return Email The email value object
     */
    public function getEmail(): Email
    {
        return $this->email;
    }

    /**
     * Get the user's password.
     *
     * @return Password The password value object
     */
    public function getPassword(): Password
    {
        return $this->password;
    }

    /**
     * Get the user's roles.
     *
     * @return array<UserRole> The user roles array
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * Check if user has a specific role.
     *
     * @param  UserRole  $role  The role to check for
     * @return bool True if user has the role, false otherwise
     */
    public function hasRole(UserRole $role): bool
    {
        return in_array($role, $this->roles);
    }

    /**
     * Compare these credentials with another for equality.
     *
     * @param  UserCredentials  $other  The credentials to compare with
     * @return bool True if all components are equal, false otherwise
     */
    public function equals(UserCredentials $other): bool
    {
        return $this->email->equals($other->email) &&
               $this->password->equals($other->password) &&
               $this->roles === $other->roles;
    }
}
