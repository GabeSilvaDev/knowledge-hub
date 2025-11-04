<?php

namespace App\Http\Controllers;

use App\Contracts\AuthServiceInterface;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthServiceInterface $authService
    ) {}

    /**
     * Register a new user.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register($request->validated());

        return response()->json($result, JsonResponse::HTTP_CREATED);
    }

    /**
     * Login user and create token.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $email = $validated['email'];
        $password = $validated['password'];

        if (! is_string($email) || ! is_string($password)) {
            return response()->json([
                'message' => 'Invalid credentials format',
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        $result = $this->authService->login(
            email: $email,
            password: $password
        );

        return response()->json($result, JsonResponse::HTTP_OK);
    }

    /**
     * Logout user (Revoke the token).
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return response()->json([
                'message' => 'Unauthenticated',
            ], JsonResponse::HTTP_UNAUTHORIZED);
        }

        $currentToken = $user->currentAccessToken();

        $this->authService->logout(
            user: $user,
            currentToken: (string) $currentToken->id
        );

        return response()->json([
            'message' => 'Logged out successfully',
        ], JsonResponse::HTTP_OK);
    }

    /**
     * Revoke all tokens for the authenticated user.
     */
    public function revokeAll(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return response()->json([
                'message' => 'Unauthenticated',
            ], JsonResponse::HTTP_UNAUTHORIZED);
        }

        $this->authService->revokeAllTokens($user);

        return response()->json([
            'message' => 'All tokens revoked successfully',
        ], JsonResponse::HTTP_OK);
    }
}
