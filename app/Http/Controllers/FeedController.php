<?php

namespace App\Http\Controllers;

use App\Contracts\FeedServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

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
     * Returns public feed for visitors, personalized feed for authenticated users.
     */
    public function index(): JsonResponse
    {
        $user = Auth::user();

        if ($user === null) {
            $feed = $this->feedService->getPublicFeed();

            return response()->json([
                'success' => true,
                'message' => 'Feed público - mostrando artigos mais populares.',
                'data' => $feed,
            ]);
        }

        $userId = $user->id;

        if (! is_string($userId)) {
            throw new RuntimeException('User ID must be a string');
        }

        $feed = $this->feedService->getPersonalizedFeed($userId);

        return response()->json([
            'success' => true,
            'message' => 'Feed personalizado baseado em suas conexões.',
            'data' => $feed,
        ]);
    }

    /**
     * Get public feed explicitly.
     */
    public function public(): JsonResponse
    {
        $feed = $this->feedService->getPublicFeed();

        return response()->json([
            'success' => true,
            'data' => $feed,
        ]);
    }
}
