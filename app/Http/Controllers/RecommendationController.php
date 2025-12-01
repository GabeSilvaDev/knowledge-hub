<?php

namespace App\Http\Controllers;

use App\Contracts\RecommendationServiceInterface;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Recommendation Controller.
 *
 * Handles HTTP requests for recommendation operations using Neo4j.
 */
final class RecommendationController extends Controller
{
    public function __construct(
        private readonly RecommendationServiceInterface $recommendationService,
    ) {}

    /**
     * Get recommended users for the authenticated user.
     *
     * Returns users with common followers (similar social circles).
     *
     * @param  Request  $request  The HTTP request with optional limit parameter
     * @return JsonResponse The list of recommended users
     */
    public function users(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        /** @var string $userId */
        $userId = $user->id;

        /** @var int $limit */
        $limit = (int) $request->query('limit', 10);

        $recommendations = $this->recommendationService->getRecommendedUsers($userId, $limit);

        return response()->json([
            'success' => true,
            'message' => $recommendations->isEmpty()
                ? 'No user recommendations available at the moment.'
                : 'Recommended users based on common followers.',
            'data' => $recommendations->toArray(),
        ]);
    }

    /**
     * Get recommended articles for the authenticated user.
     *
     * Returns articles based on tags and categories the user interacts with.
     *
     * @param  Request  $request  The HTTP request with optional limit parameter
     * @return JsonResponse The list of recommended articles
     */
    public function articles(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        /** @var string $userId */
        $userId = $user->id;

        /** @var int $limit */
        $limit = (int) $request->query('limit', 10);

        $recommendations = $this->recommendationService->getRecommendedArticles($userId, $limit);

        return response()->json([
            'success' => true,
            'message' => $recommendations->isEmpty()
                ? 'No article recommendations available at the moment.'
                : 'Recommended articles based on your interests.',
            'data' => $recommendations->toArray(),
        ]);
    }

    /**
     * Get related articles for a specific article.
     *
     * Returns articles with similar tags and categories.
     *
     * @param  Request  $request  The HTTP request with optional limit parameter
     * @param  string  $articleId  The article ID to find related articles for
     * @return JsonResponse The list of related articles
     */
    public function related(Request $request, string $articleId): JsonResponse
    {
        /** @var int $limit */
        $limit = (int) $request->query('limit', 10);

        $recommendations = $this->recommendationService->getRelatedArticles($articleId, $limit);

        return response()->json([
            'success' => true,
            'message' => $recommendations->isEmpty()
                ? 'No related articles found.'
                : 'Related articles by tags and categories.',
            'data' => $recommendations->toArray(),
        ]);
    }

    /**
     * Get recommended authors.
     *
     * Returns influential authors based on follower network.
     *
     * @param  Request  $request  The HTTP request with optional limit parameter
     * @return JsonResponse The list of influential authors
     */
    public function authors(Request $request): JsonResponse
    {
        /** @var int $limit */
        $limit = (int) $request->query('limit', 10);

        $recommendations = $this->recommendationService->getRecommendedAuthors($limit);

        return response()->json([
            'success' => true,
            'message' => $recommendations->isEmpty()
                ? 'No influential authors found at the moment.'
                : 'Influential authors on the platform.',
            'data' => $recommendations->toArray(),
        ]);
    }

    /**
     * Get topics of interest for the authenticated user.
     *
     * Returns topics/tags based on user's likes and interactions.
     *
     * @param  Request  $request  The HTTP request with optional limit parameter
     * @return JsonResponse The list of topics of interest
     */
    public function topics(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        /** @var string $userId */
        $userId = $user->id;

        /** @var int $limit */
        $limit = (int) $request->query('limit', 10);

        $recommendations = $this->recommendationService->getTopicsOfInterest($userId, $limit);

        return response()->json([
            'success' => true,
            'message' => $recommendations->isEmpty()
                ? 'No topics of interest identified yet.'
                : 'Topics based on your interactions.',
            'data' => $recommendations->toArray(),
        ]);
    }

    /**
     * Sync data from MongoDB to Neo4j.
     *
     * Admin-only endpoint to synchronize graph data.
     *
     * @return JsonResponse The synchronization statistics
     */
    public function sync(): JsonResponse
    {
        $stats = $this->recommendationService->syncFromDatabase();

        return response()->json([
            'success' => true,
            'message' => 'Neo4j synchronization completed successfully.',
            'data' => [
                'synced' => $stats,
                'neo4j_available' => $this->recommendationService->isAvailable(),
            ],
        ]);
    }

    /**
     * Get Neo4j graph statistics.
     *
     * @return JsonResponse The graph statistics and availability status
     */
    public function statistics(): JsonResponse
    {
        $stats = $this->recommendationService->getStatistics();
        $isAvailable = $this->recommendationService->isAvailable();

        return response()->json([
            'success' => true,
            'message' => $isAvailable
                ? 'Recommendation graph statistics.'
                : 'Neo4j is not available at the moment.',
            'data' => [
                'neo4j_available' => $isAvailable,
                'statistics' => $stats,
            ],
        ]);
    }
}
