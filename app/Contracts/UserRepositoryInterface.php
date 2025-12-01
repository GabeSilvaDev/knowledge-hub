<?php

namespace App\Contracts;

use App\DTOs\CreateUserDTO;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * User repository contract.
 *
 * Defines the interface for user data access operations.
 */
interface UserRepositoryInterface
{
    /**
     * Find user by ID.
     *
     * Retrieves a user by their unique identifier.
     *
     * @param  string  $id  The user ID
     * @return User|null The user or null if not found
     */
    public function findById(string $id): ?User;

    /**
     * Find user by email.
     *
     * Retrieves a user by their email address.
     *
     * @param  string  $email  The email address
     * @return User|null The user or null if not found
     */
    public function findByEmail(string $email): ?User;

    /**
     * Find user by username.
     *
     * Retrieves a user by their username.
     *
     * @param  string  $username  The username
     * @return User|null The user or null if not found
     */
    public function findByUsername(string $username): ?User;

    /**
     * Create a new user.
     *
     * Persists a new user to the database using the provided DTO.
     *
     * @param  CreateUserDTO  $dto  Data transfer object with user data
     * @return User The newly created user instance
     */
    public function create(CreateUserDTO $dto): User;

    /**
     * Update user data.
     *
     * Updates a user with the provided data.
     *
     * @param  User  $user  The user to update
     * @param  array<string, mixed>  $data  The data to update
     * @return User The updated user instance
     */
    public function update(User $user, array $data): User;

    /**
     * Get paginated users.
     *
     * Retrieves a paginated list of all users.
     *
     * @param  int  $perPage  Number of users per page (default: 15)
     * @return LengthAwarePaginator<int, User> Paginated users
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    /**
     * Get users by role.
     *
     * Retrieves all users with the specified role.
     *
     * @param  string  $role  The role to filter by
     * @return Collection<int, User> Collection of users
     */
    public function getByRole(string $role): Collection;

    /**
     * Search users by name or username.
     *
     * Searches for users matching the term across name, username, and email.
     *
     * @param  string  $term  The search term
     * @return Collection<int, User> Collection of matching users
     */
    public function search(string $term): Collection;

    /**
     * Update last login timestamp.
     *
     * Sets the last_login_at field to current timestamp.
     *
     * @param  User  $user  The user to update
     */
    public function updateLastLogin(User $user): void;

    /**
     * Revoke a specific token.
     *
     * Deletes the specified token from the user's tokens.
     *
     * @param  User  $user  The user who owns the token
     * @param  string  $tokenId  The token ID to revoke
     */
    public function revokeToken(User $user, string $tokenId): void;

    /**
     * Revoke all tokens for a user.
     *
     * Deletes all tokens belonging to the user.
     *
     * @param  User  $user  The user to revoke tokens for
     */
    public function revokeAllTokens(User $user): void;
}
