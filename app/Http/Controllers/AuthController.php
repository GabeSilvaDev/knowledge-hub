<?php

namespace App\Http\Controllers;

use App\Contracts\AuthServiceInterface;
use App\DTOs\CreateUserDTO;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Response;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthServiceInterface $authService
    ) {
    }

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
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $result = $this->authService->login(
            email: $validated['email'],
            password: $validated['password']
        );

        return response()->json($result, JsonResponse::HTTP_OK);
    }

    /**
     * Logout user (Revoke the token).
     */
    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout(
            user: $request->user(),
            currentToken: $request->user()->currentAccessToken()->id
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
        $this->authService->revokeAllTokens($request->user());

        return response()->json([
            'message' => 'All tokens revoked successfully',
        ], JsonResponse::HTTP_OK);
    }
}
