<?php

namespace App\Http\Controllers;

use App\Contracts\ArticleRankingServiceInterface;
use App\Models\Article;
use Illuminate\Http\JsonResponse;

class ArticleRankingController extends Controller
{
    public function __construct(
        private readonly ArticleRankingServiceInterface $rankingService
    ) {}

    /**
     * Get top ranked articles in real-time.
     */
    public function index(): JsonResponse
    {
        $limit = (int) request()->query('limit', 10);
        $limit = min($limit, 100);

        $ranking = $this->rankingService->getTopArticles($limit);

        /** @var array<int, string> $articleIds */
        $articleIds = $ranking->pluck('article_id')->toArray();
        $articles = Article::whereIn('_id', $articleIds)->get()->keyBy('id');

        $enrichedRanking = $ranking->map(function (array $item, int|string $index) use ($articles): array {
            /** @var Article|null $article */
            $article = $articles->get($item['article_id']);

            return [
                'rank' => (int) $index + 1,
                'article_id' => $item['article_id'],
                'views' => (int) $item['score'],
                'article' => $article ? [
                    'title' => $article->title,
                    'slug' => $article->slug,
                    'excerpt' => $article->excerpt,
                    'author_id' => $article->author_id,
                    'published_at' => $article->published_at?->toISOString(),
                ] : null,
            ];
        });

        return response()->json([
            'data' => $enrichedRanking->values()->toArray(),
        ], JsonResponse::HTTP_OK);
    }

    /**
     * Get ranking statistics.
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
     */
    public function sync(): JsonResponse
    {
        $this->rankingService->syncFromDatabase();

        return response()->json([
            'message' => 'Ranking sincronizado com sucesso.',
        ], JsonResponse::HTTP_OK);
    }

    /**
     * Get specific article ranking info.
     */
    public function show(Article $article): JsonResponse
    {
        $articleId = $article->id;
        assert(is_string($articleId));

        $rank = $this->rankingService->getArticleRank($articleId);
        $score = $this->rankingService->getArticleScore($articleId);

        return response()->json([
            'data' => [
                'article_id' => $articleId,
                'rank' => $rank,
                'views' => (int) $score,
                'article' => [
                    'title' => $article->title,
                    'slug' => $article->slug,
                    'view_count' => $article->view_count,
                ],
            ],
        ], JsonResponse::HTTP_OK);
    }
}
