<?php

namespace App\Contracts;

use App\DTOs\CreateUserDTO;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface UserRepositoryInterface
{
    /**
     * Find user by ID.
     */
    public function findById(string $id): ?User;

    /**
     * Find user by email.
     */
    public function findByEmail(string $email): ?User;

    /**
     * Find user by username.
     */
    public function findByUsername(string $username): ?User;

    /**
     * Create a new user.
     */
    public function create(CreateUserDTO $dto): User;

    /**
     * Get paginated users.
     *
     * @return LengthAwarePaginator<int, User>
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    /**
     * Get users by role.
     *
     * @return Collection<int, User>
     */
    public function getByRole(string $role): Collection;

    /**
     * Search users by name or username.
     *
     * @return Collection<int, User>
     */
    public function search(string $term): Collection;

    /**
     * Update last login timestamp.
     */
    public function updateLastLogin(User $user): void;

    /**
     * Revoke a specific token.
     */
    public function revokeToken(User $user, string $tokenId): void;

    /**
     * Revoke all tokens for a user.
     */
    public function revokeAllTokens(User $user): void;
}
