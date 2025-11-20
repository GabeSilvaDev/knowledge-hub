<?php

namespace App\Repositories;

use App\Contracts\UserRepositoryInterface;
use App\DTOs\CreateUserDTO;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class UserRepository implements UserRepositoryInterface
{
    /**
     * Initialize the User Repository.
     *
     * Constructs the repository with the User model instance.
     *
     * @param  User  $model  The User model instance
     */
    public function __construct(
        private readonly User $model
    ) {}

    /**
     * Find user by ID.
     *
     * Retrieves a user by their unique identifier.
     *
     * @param  string  $id  The user ID
     * @return User|null The user instance or null if not found
     */
    public function findById(string $id): ?User
    {
        return $this->model->find($id);
    }

    /**
     * Find user by email.
     *
     * Retrieves a user by their email address.
     *
     * @param  string  $email  The user email address
     * @return User|null The user instance or null if not found
     */
    public function findByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }

    /**
     * Find user by username.
     *
     * Retrieves a user by their username.
     *
     * @param  string  $username  The username
     * @return User|null The user instance or null if not found
     */
    public function findByUsername(string $username): ?User
    {
        return $this->model->where('username', $username)->first();
    }

    /**
     * Create a new user.
     *
     * Persists a new user to the database using the provided DTO data.
     *
     * @param  CreateUserDTO  $dto  Data transfer object containing user creation data
     * @return User The newly created user instance
     */
    public function create(CreateUserDTO $dto): User
    {
        return $this->model->create($dto->toArray());
    }

    /**
     * Get paginated users.
     *
     * Retrieves a paginated list of all users.
     *
     * @param  int  $perPage  Number of users per page (default: 15)
     * @return LengthAwarePaginator<int, User> Paginated collection of users
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->paginate($perPage);
    }

    /**
     * Get users by role.
     *
     * Retrieves all users with the specified role.
     *
     * @param  string  $role  The role to filter users by
     * @return Collection<int, User> Collection of users with the specified role
     */
    public function getByRole(string $role): Collection
    {
        return $this->model->where('roles', 'like', "%{$role}%")->get();
    }

    /**
     * Search users.
     *
     * Searches for users matching the term across name, username, and email.
     *
     * @param  string  $term  The search term
     * @return Collection<int, User> Collection of matching users
     */
    public function search(string $term): Collection
    {
        return $this->model
            ->where('name', 'like', "%{$term}%")
            ->orWhere('username', 'like', "%{$term}%")
            ->orWhere('email', 'like', "%{$term}%")
            ->get();
    }

    /**
     * Update last login timestamp.
     *
     * Sets the last_login_at field to the current timestamp.
     *
     * @param  User  $user  The user to update
     */
    public function updateLastLogin(User $user): void
    {
        $user->update(['last_login_at' => now()]);
    }

    /**
     * Revoke a specific token.
     *
     * Deletes the specified token from the user's tokens.
     *
     * @param  User  $user  The user who owns the token
     * @param  string  $tokenId  The token ID to revoke
     */
    public function revokeToken(User $user, string $tokenId): void
    {
        $user->tokens()->find($tokenId)?->delete();
    }

    /**
     * Revoke all tokens.
     *
     * Deletes all tokens belonging to the user.
     *
     * @param  User  $user  The user to revoke all tokens for
     */
    public function revokeAllTokens(User $user): void
    {
        $user->tokens()->delete();
    }
}
