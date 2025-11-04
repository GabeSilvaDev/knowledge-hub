<?php

namespace App\Contracts;

use App\Models\User;
use Illuminate\Validation\ValidationException;

interface AuthServiceInterface
{
    /**
     * Register a new user.
     *
     * @param  array<string, mixed>  $data
     * @return array{user: User, token: string}
     */
    public function register(array $data): array;

    /**
     * Authenticate a user and generate token.
     *
     * @return array{user: User, token: string}
     *
     * @throws ValidationException
     */
    public function login(string $email, string $password): array;

    /**
     * Revoke the current access token.
     */
    public function logout(User $user, string $currentToken): void;

    /**
     * Revoke all tokens for a user.
     */
    public function revokeAllTokens(User $user): void;
}
