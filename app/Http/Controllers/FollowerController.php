<?php

namespace App\Http\Controllers;

use App\Contracts\FollowerServiceInterface;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;
use RuntimeException;

/**
 * Follower Controller.
 *
 * Handles HTTP requests for follower operations.
 */
final class FollowerController extends Controller
{
    public function __construct(
        private readonly FollowerServiceInterface $followerService,
    ) {}

    /**
     * Toggle follow for a user.
     */
    public function toggle(User $user): JsonResponse
    {
        $currentUserId = Auth::id();

        if ($currentUserId === null) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não autenticado.',
            ], JsonResponse::HTTP_UNAUTHORIZED);
        }

        if (! is_string($currentUserId) || ! is_string($user->id)) {
            throw new RuntimeException('User IDs must be strings');
        }

        try {
            $result = $this->followerService->toggleFollow($currentUserId, $user->id);

            return response()->json([
                'success' => true,
                'message' => $result['following'] ? 'Usuário seguido com sucesso.' : 'Você deixou de seguir este usuário.',
                'data' => [
                    'following' => $result['following'],
                    'counts' => $this->followerService->getCounts($user->id),
                ],
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], JsonResponse::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Get followers for a user.
     */
    public function followers(User $user): JsonResponse
    {
        if (! is_string($user->id)) {
            throw new RuntimeException('User ID must be a string');
        }

        $followers = $this->followerService->getFollowers($user->id);

        return response()->json([
            'success' => true,
            'data' => $followers,
        ]);
    }

    /**
     * Get users being followed by a user.
     */
    public function following(User $user): JsonResponse
    {
        if (! is_string($user->id)) {
            throw new RuntimeException('User ID must be a string');
        }

        $following = $this->followerService->getFollowing($user->id);

        return response()->json([
            'success' => true,
            'data' => $following,
        ]);
    }

    /**
     * Check if current user is following a user.
     */
    public function check(User $user): JsonResponse
    {
        $currentUserId = Auth::id();

        if ($currentUserId === null) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não autenticado.',
            ], JsonResponse::HTTP_UNAUTHORIZED);
        }

        if (! is_string($currentUserId) || ! is_string($user->id)) {
            throw new RuntimeException('User IDs must be strings');
        }

        $isFollowing = $this->followerService->isFollowing($currentUserId, $user->id);

        return response()->json([
            'success' => true,
            'data' => [
                'is_following' => $isFollowing,
            ],
        ]);
    }
}
