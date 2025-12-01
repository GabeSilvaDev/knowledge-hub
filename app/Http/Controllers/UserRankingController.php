<?php

namespace App\Http\Controllers;

use App\Contracts\UserRankingServiceInterface;
use App\Models\User;
use Illuminate\Http\JsonResponse;

/**
 * User Ranking Controller.
 *
 * Handles HTTP requests for user influence ranking operations.
 * Implements RF-051: Influence-Based User Ranking.
 */
class UserRankingController extends Controller
{
    /**
     * UserRankingController constructor.
     *
     * @param  UserRankingServiceInterface  $rankingService  Service for handling user ranking operations
     */
    public function __construct(
        private readonly UserRankingServiceInterface $rankingService
    ) {}

    /**
     * Get top ranked users by influence.
     *
     * Returns a list of the top-ranked users based on influence scores from Redis cache.
     * Results include user details and ranking information.
     *
     * @return JsonResponse List of top-ranked users with their details
     */
    public function index(): JsonResponse
    {
        $limit = (int) request()->query('limit', 10);
        $limit = min($limit, 100);

        $enrichedRanking = $this->rankingService->getEnrichedTopUsers($limit);

        return response()->json([
            'data' => $enrichedRanking->values()->toArray(),
        ], JsonResponse::HTTP_OK);
    }

    /**
     * Get ranking statistics.
     *
     * Returns overall statistics about the user ranking system, including
     * total users, total score, highest score, and average score.
     *
     * @return JsonResponse Ranking statistics
     */
    public function statistics(): JsonResponse
    {
        $stats = $this->rankingService->getStatistics();

        return response()->json([
            'data' => $stats,
        ], JsonResponse::HTTP_OK);
    }

    /**
     * Sync ranking from database.
     *
     * Synchronizes the Redis ranking cache with current user statistics from the database.
     * This operation resets the existing ranking and rebuilds it from all users.
     *
     * @return JsonResponse Success message
     */
    public function sync(): JsonResponse
    {
        $this->rankingService->syncFromDatabase();

        return response()->json([
            'message' => 'User ranking synchronized successfully.',
        ], JsonResponse::HTTP_OK);
    }

    /**
     * Get specific user ranking info.
     *
     * Returns detailed ranking information for a specific user including
     * their rank position, influence score, and breakdown of metrics.
     *
     * @param  User  $user  The user to get ranking info for (route model binding)
     * @return JsonResponse User ranking information
     */
    public function show(User $user): JsonResponse
    {
        $userId = $user->id;
        assert(is_string($userId));

        $rankingData = $this->rankingService->getEnrichedUserRanking($userId);

        return response()->json([
            'data' => $rankingData->toArray(),
            'breakdown' => $rankingData->getInfluenceBreakdown(),
        ], JsonResponse::HTTP_OK);
    }

    /**
     * Recalculate ranking for a specific user.
     *
     * Recalculates and updates the influence score for a specific user.
     *
     * @param  User  $user  The user to recalculate (route model binding)
     * @return JsonResponse Updated ranking information
     */
    public function recalculate(User $user): JsonResponse
    {
        $userId = $user->id;
        assert(is_string($userId));

        $this->rankingService->recalculateUser($userId);
        $rankingData = $this->rankingService->getEnrichedUserRanking($userId);

        return response()->json([
            'message' => 'User ranking recalculated successfully.',
            'data' => $rankingData->toArray(),
        ], JsonResponse::HTTP_OK);
    }
}
