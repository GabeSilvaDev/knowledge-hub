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
        private readonly UserRepositoryInterface $userRepository,
        private readonly TokenService $tokenService
    ) {}

    /**
     * Register a new user.
     *
     * @param  array<string, mixed>  $data
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

        $tokenResult = $this->tokenService->createToken($user, 'auth_token');

        return [
            'user' => $user,
            'token' => $tokenResult->plainTextToken,
        ];
    }

    /**
     * Authenticate a user and generate token.
     *
     * @return array{user: User, token: string}
     *
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

        $tokenResult = $this->tokenService->createToken($user, 'auth_token');

        return [
            'user' => $user->refresh(),
            'token' => $tokenResult->plainTextToken,
        ];
    }

    /**
     * Revoke the current access token.
     */
    public function logout(User $user, string $currentToken): void
    {
        $tokenId = $this->tokenService->extractTokenId($currentToken);

        $this->tokenService->revokeToken($tokenId);

        $this->userRepository->revokeToken($user, $currentToken);
    }

    /**
     * Revoke all tokens for a user.
     */
    public function revokeAllTokens(User $user): void
    {
        $userId = $user->getKey();
        if (is_string($userId) || is_int($userId)) {
            $this->tokenService->revokeAllUserTokens((string) $userId);
        }

        $this->userRepository->revokeAllTokens($user);
    }
}
