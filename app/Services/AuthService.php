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
    /**
     * Initialize the Authentication Service.
     *
     * Constructs the service with injected repository and token service dependencies.
     *
     * @param  UserRepositoryInterface  $userRepository  Repository for user data access
     * @param  TokenService  $tokenService  Service for token management
     */
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly TokenService $tokenService
    ) {}

    /**
     * Register a new user.
     *
     * Creates a new user account with the provided data and generates an authentication token.
     *
     * @param  array<string, mixed>  $data  User registration data
     * @return array{user: User, token: string} Array containing the created user and access token
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
     * Validates user credentials, updates last login timestamp, and generates a new access token.
     *
     * @param  string  $email  The user's email address
     * @param  string  $password  The user's password
     * @return array{user: User, token: string} Array containing the authenticated user and access token
     *
     * @throws ValidationException If credentials are invalid
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
     *
     * Invalidates the current authentication token in both Redis and database.
     *
     * @param  User  $user  The authenticated user
     * @param  string  $currentToken  The token to revoke
     */
    public function logout(User $user, string $currentToken): void
    {
        $tokenId = $this->tokenService->extractTokenId($currentToken);

        $this->tokenService->revokeToken($tokenId);

        $this->userRepository->revokeToken($user, $currentToken);
    }

    /**
     * Revoke all tokens for a user.
     *
     * Invalidates all authentication tokens belonging to the user in both Redis and database.
     *
     * @param  User  $user  The user to revoke all tokens for
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
