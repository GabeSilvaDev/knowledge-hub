<?php

namespace App\Http\Controllers;

use App\Contracts\FeedServiceInterface;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

/**
 * Feed Controller.
 *
 * Handles HTTP requests for feed operations.
 */
final class FeedController extends Controller
{
    public function __construct(
        private readonly FeedServiceInterface $feedService,
    ) {}

    /**
     * Get feed based on authentication status.
     *
     * Returns public feed for visitors, personalized feed for authenticated users.
     *
     * @return JsonResponse The feed articles
     */
    public function index(): JsonResponse
    {
        $user = Auth::user();

        if ($user === null) {
            $feed = $this->feedService->getPublicFeed();

            return response()->json([
                'success' => true,
                'message' => 'Public feed - showing most popular articles.',
                'data' => $feed,
            ]);
        }

        /** @var string $userId */
        $userId = $user->id;

        $feed = $this->feedService->getPersonalizedFeed($userId);

        return response()->json([
            'success' => true,
            'message' => 'Personalized feed based on your connections.',
            'data' => $feed,
        ]);
    }

    /**
     * Get public feed explicitly.
     *
     * @return JsonResponse The public feed with popular articles
     */
    public function public(): JsonResponse
    {
        $feed = $this->feedService->getPublicFeed();

        return response()->json([
            'success' => true,
            'data' => $feed,
        ]);
    }

    /**
     * Get personalized feed for authenticated user.
     *
     * @return JsonResponse The personalized feed based on user connections
     */
    public function personalized(): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        /** @var string $userId */
        $userId = $user->id;

        $feed = $this->feedService->getPersonalizedFeed($userId);

        return response()->json([
            'success' => true,
            'message' => 'Personalized feed based on your connections.',
            'data' => $feed,
        ]);
    }
}
