<?php

namespace App\Http\Controllers;

use App\Contracts\ArticleRankingServiceInterface;
use App\Models\Article;
use Illuminate\Http\JsonResponse;

class ArticleRankingController extends Controller
{
    /**
     * ArticleRankingController constructor.
     *
     * Initializes the controller with article ranking service dependency.
     *
     * @param  ArticleRankingServiceInterface  $rankingService  Service for handling article ranking operations
     */
    public function __construct(
        private readonly ArticleRankingServiceInterface $rankingService
    ) {}

    /**
     * Get top ranked articles in real-time.
     *
     * Returns a list of the top-ranked articles based on view counts from Redis cache.
     * Results include article details and ranking information.
     *
     * @return JsonResponse List of top-ranked articles with their details
     */
    public function index(): JsonResponse
    {
        $limit = (int) request()->query('limit', 10);
        $limit = min($limit, 100);

        $enrichedRanking = $this->rankingService->getEnrichedTopArticles($limit);

        return response()->json([
            'data' => $enrichedRanking->values()->toArray(),
        ], JsonResponse::HTTP_OK);
    }

    /**
     * Get ranking statistics.
     *
     * Returns overall statistics about the article ranking system, including
     * total articles, total views, and highest score.
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
     * Synchronizes the Redis ranking cache with current view counts from the database.
     * This operation resets the existing ranking and rebuilds it from published articles.
     *
     * @return JsonResponse Success message
     */
    public function sync(): JsonResponse
    {
        $this->rankingService->syncFromDatabase();

        return response()->json([
            'message' => 'Ranking synchronized successfully.',
        ], JsonResponse::HTTP_OK);
    }

    /**
     * Get specific article ranking info.
     *
     * Returns detailed ranking information for a specific article including
     * its rank position, view count, and article details.
     *
     * @param  Article  $article  The article to get ranking info for (route model binding)
     * @return JsonResponse Article ranking information
     */
    public function show(Article $article): JsonResponse
    {
        $articleId = $article->id;
        assert(is_string($articleId));

        $rankingData = $this->rankingService->getEnrichedArticleRanking($articleId);

        return response()->json([
            'data' => $rankingData,
        ], JsonResponse::HTTP_OK);
    }
}
