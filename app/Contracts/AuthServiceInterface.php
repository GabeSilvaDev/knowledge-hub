<?php

namespace App\Contracts;

use Illuminate\Validation\ValidationException;
use App\Models\User;

interface AuthServiceInterface
{
    /**
     * Register a new user.
     *
     * @param array $data
     * @return array{user: User, token: string}
     */
    public function register(array $data): array;

    /**
     * Authenticate a user and generate token.
     *
     * @param string $email
     * @param string $password
     * @return array{user: User, token: string}
     * @throws ValidationException
     */
    public function login(string $email, string $password): array;

    /**
     * Revoke the current access token.
     *
     * @param User $user
     * @param string $currentToken
     * @return void
     */
    public function logout(User $user, string $currentToken): void;

    /**
     * Revoke all tokens for a user.
     *
     * @param User $user
     * @return void
     */
    public function revokeAllTokens(User $user): void;
}
