<?php

namespace App\Contracts;

use App\Models\User;
use Illuminate\Validation\ValidationException;

/**
 * Authentication service contract.
 *
 * Defines the interface for user authentication and token management operations.
 */
interface AuthServiceInterface
{
    /**
     * Register a new user.
     *
     * Creates a new user account and generates authentication token.
     *
     * @param  array<string, mixed>  $data  User registration data
     * @return array{user: User, token: string} The user and token
     */
    public function register(array $data): array;

    /**
     * Authenticate a user and generate token.
     *
     * Validates credentials and generates authentication token.
     *
     * @param  string  $email  The user email
     * @param  string  $password  The user password
     * @return array{user: User, token: string} The user and token
     *
     * @throws ValidationException If credentials are invalid
     */
    public function login(string $email, string $password): array;

    /**
     * Revoke the current access token.
     *
     * Invalidates the specified authentication token.
     *
     * @param  User  $user  The authenticated user
     * @param  string  $currentToken  The token to revoke
     */
    public function logout(User $user, string $currentToken): void;

    /**
     * Revoke all tokens for a user.
     *
     * Invalidates all authentication tokens belonging to the user.
     *
     * @param  User  $user  The user to revoke tokens for
     */
    public function revokeAllTokens(User $user): void;
}
