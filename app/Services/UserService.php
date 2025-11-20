<?php

namespace App\Services;

use App\Contracts\UserRepositoryInterface;
use App\DTOs\CreateUserDTO;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class UserService
{
    /**
     * Initialize the User Service.
     *
     * Constructs the service with injected repository for user data access.
     *
     * @param  UserRepositoryInterface  $userRepository  Repository for user data access
     */
    public function __construct(
        private readonly UserRepositoryInterface $userRepository
    ) {}

    /**
     * Create a new user.
     *
     * Creates a new user in the system using the provided DTO.
     *
     * @param  CreateUserDTO  $dto  Data transfer object containing user creation data
     * @return User The newly created user instance
     */
    public function createUser(CreateUserDTO $dto): User
    {
        return $this->userRepository->create($dto);
    }

    /**
     * Get user by ID.
     *
     * Retrieves a user by their unique identifier.
     *
     * @param  string  $id  The user ID
     * @return User|null The user instance or null if not found
     */
    public function getUserById(string $id): ?User
    {
        return $this->userRepository->findById($id);
    }

    /**
     * Update user profile.
     *
     * @param User $user
     * @param array<string, mixed> $data
     * @return User
     */
    public function updateUser(User $user, array $data): User
    {
        return $this->userRepository->update($user, $data);
    }

    /**
     * Get user by email.
     *
     * Retrieves a user by their email address.
     *
     * @param  string  $email  The user email address
     * @return User|null The user instance or null if not found
     */
    public function getUserByEmail(string $email): ?User
    {
        return $this->userRepository->findByEmail($email);
    }

    /**
     * Get user by username.
     *
     * Retrieves a user by their username.
     *
     * @param  string  $username  The username
     * @return User|null The user instance or null if not found
     */
    public function getUserByUsername(string $username): ?User
    {
        return $this->userRepository->findByUsername($username);
    }

    /**
     * Get paginated users.
     *
     * Retrieves a paginated list of all users in the system.
     *
     * @param  int  $perPage  Number of users per page (default: 15)
     * @return LengthAwarePaginator<int, User> Paginated collection of users
     */
    public function getUsers(int $perPage = 15): LengthAwarePaginator
    {
        return $this->userRepository->paginate($perPage);
    }

    /**
     * Get users by role.
     *
     * Retrieves all users with the specified role.
     *
     * @param  UserRole  $role  The role to filter users by
     * @return Collection<int, User> Collection of users with the specified role
     */
    public function getUsersByRole(UserRole $role): Collection
    {
        return $this->userRepository->getByRole($role->value);
    }

    /**
     * Search users.
     *
     * Searches for users matching the provided search term across name, email, and username.
     *
     * @param  string  $term  The search term
     * @return Collection<int, User> Collection of matching users
     */
    public function searchUsers(string $term): Collection
    {
        return $this->userRepository->search($term);
    }
}
