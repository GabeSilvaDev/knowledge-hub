<?php

namespace App\Http\Controllers;

use App\Contracts\FollowerServiceInterface;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;

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
        /** @var string $currentUserId */
        $currentUserId = Auth::id();
        /** @var string $targetUserId */
        $targetUserId = $user->id;

        try {
            $result = $this->followerService->toggleFollow($currentUserId, $targetUserId);

            return response()->json([
                'success' => true,
                'message' => $result['following'] ? 'Usuário seguido com sucesso.' : 'Você deixou de seguir este usuário.',
                'data' => [
                    'following' => $result['following'],
                    'counts' => $this->followerService->getCounts($targetUserId),
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
        /** @var string $userId */
        $userId = $user->id;
        $followers = $this->followerService->getFollowers($userId);

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
        /** @var string $userId */
        $userId = $user->id;
        $following = $this->followerService->getFollowing($userId);

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
        /** @var string $currentUserId */
        $currentUserId = Auth::id();
        /** @var string $targetUserId */
        $targetUserId = $user->id;

        $isFollowing = $this->followerService->isFollowing($currentUserId, $targetUserId);

        return response()->json([
            'success' => true,
            'data' => [
                'is_following' => $isFollowing,
            ],
        ]);
    }
}
