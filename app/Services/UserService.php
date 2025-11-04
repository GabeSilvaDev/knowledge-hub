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
    public function __construct(
        private readonly UserRepositoryInterface $userRepository
    ) {}

    /**
     * Create a new user.
     */
    public function createUser(CreateUserDTO $dto): User
    {
        return $this->userRepository->create($dto);
    }

    /**
     * Get user by ID.
     */
    public function getUserById(string $id): ?User
    {
        return $this->userRepository->findById($id);
    }

    /**
     * Get user by email.
     */
    public function getUserByEmail(string $email): ?User
    {
        return $this->userRepository->findByEmail($email);
    }

    /**
     * Get user by username.
     */
    public function getUserByUsername(string $username): ?User
    {
        return $this->userRepository->findByUsername($username);
    }

    /**
     * Get paginated users.
     *
     * @return LengthAwarePaginator<int, User>
     */
    public function getUsers(int $perPage = 15): LengthAwarePaginator
    {
        return $this->userRepository->paginate($perPage);
    }

    /**
     * Get users by role.
     *
     * @return Collection<int, User>
     */
    public function getUsersByRole(UserRole $role): Collection
    {
        return $this->userRepository->getByRole($role->value);
    }

    /**
     * Search users.
     *
     * @return Collection<int, User>
     */
    public function searchUsers(string $term): Collection
    {
        return $this->userRepository->search($term);
    }
}
