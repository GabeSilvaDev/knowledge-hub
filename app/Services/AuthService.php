<?php

namespace App\Services;

use App\Contracts\AuthServiceInterface;
use App\Contracts\UserRepositoryInterface;
use App\DTOs\CreateUserDTO;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService implements AuthServiceInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository
    ) {
    }

    /**
     * Register a new user.
     *
     * @param array $data
     * @return array{user: User, token: string}
     */
    public function register(array $data): array
    {
        $dto = CreateUserDTO::fromArray([
            'name' => $data['name'],
            'email' => $data['email'],
            'username' => $data['username'],
            'password' => $data['password'],
            'bio' => $data['bio'] ?? null,
            'avatar_url' => $data['avatar_url'] ?? null,
            'roles' => [UserRole::READER],
        ]);

        $user = $this->userRepository->create($dto);

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * Authenticate a user and generate token.
     *
     * @param string $email
     * @param string $password
     * @return array{user: User, token: string}
     * @throws ValidationException
     */
    public function login(string $email, string $password): array
    {
        $user = $this->userRepository->findByEmail($email);

        if (! $user || ! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $this->userRepository->updateLastLogin($user);

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user->refresh(),
            'token' => $token,
        ];
    }

    /**
     * Revoke the current access token.
     *
     * @param User $user
     * @param string $currentToken
     * @return void
     */
    public function logout(User $user, string $currentToken): void
    {
        $this->userRepository->revokeToken($user, $currentToken);
    }

    /**
     * Revoke all tokens for a user.
     *
     * @param User $user
     * @return void
     */
    public function revokeAllTokens(User $user): void
    {
        $this->userRepository->revokeAllTokens($user);
    }
}
