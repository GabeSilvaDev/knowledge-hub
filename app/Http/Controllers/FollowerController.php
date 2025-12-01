<?php

namespace App\Http\Controllers;

use App\Contracts\FollowerServiceInterface;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

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
     *
     * @param  User  $user  The user to follow/unfollow
     * @return JsonResponse The follow status and updated counts
     */
    public function toggle(User $user): JsonResponse
    {
        /** @var string $currentUserId */
        $currentUserId = Auth::id();
        /** @var string $targetUserId */
        $targetUserId = $user->id;

        $result = $this->followerService->toggleFollow($currentUserId, $targetUserId);

        return response()->json([
            'success' => true,
            'message' => $result['following'] ? 'User followed successfully.' : 'You have unfollowed this user.',
            'data' => [
                'following' => $result['following'],
                'counts' => $this->followerService->getCounts($targetUserId),
            ],
        ]);
    }

    /**
     * Get followers for a user.
     *
     * @param  User  $user  The user to get followers for
     * @return JsonResponse The list of followers
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
     *
     * @param  User  $user  The user to get following list for
     * @return JsonResponse The list of users being followed
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
     *
     * @param  User  $user  The user to check follow status for
     * @return JsonResponse The follow status
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
