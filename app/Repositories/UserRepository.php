<?php

namespace App\Repositories;

use App\Contracts\UserRepositoryInterface;
use App\DTOs\CreateUserDTO;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class UserRepository implements UserRepositoryInterface
{
    public function __construct(
        private readonly User $model
    ) {}

    public function findById(string $id): ?User
    {
        return $this->model->find($id);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }

    public function findByUsername(string $username): ?User
    {
        return $this->model->where('username', $username)->first();
    }

    public function create(CreateUserDTO $dto): User
    {
        return $this->model->create($dto->toArray());
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->paginate($perPage);
    }

    public function getByRole(string $role): Collection
    {
        return $this->model->where('roles', 'like', "%{$role}%")->get();
    }

    public function search(string $term): Collection
    {
        return $this->model
            ->where('name', 'like', "%{$term}%")
            ->orWhere('username', 'like', "%{$term}%")
            ->orWhere('email', 'like', "%{$term}%")
            ->get();
    }

    public function updateLastLogin(User $user): void
    {
        $user->update(['last_login_at' => now()]);
    }

    public function revokeToken(User $user, string $tokenId): void
    {
        $user->tokens()->find($tokenId)?->delete();
    }

    public function revokeAllTokens(User $user): void
    {
        $user->tokens()->delete();
    }
}
