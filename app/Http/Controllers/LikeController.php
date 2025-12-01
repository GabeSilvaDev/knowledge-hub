<?php

namespace App\Http\Controllers;

use App\Contracts\LikeServiceInterface;
use App\Models\Article;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

/**
 * Like Controller.
 *
 * Handles HTTP requests for like operations.
 */
final class LikeController extends Controller
{
    public function __construct(
        private readonly LikeServiceInterface $likeService,
    ) {}

    /**
     * Toggle like on an article.
     */
    public function toggle(Article $article): JsonResponse
    {
        /** @var string $userId */
        $userId = Auth::id();
        /** @var string $articleId */
        $articleId = $article->id;

        $result = $this->likeService->toggleLike($articleId, $userId);

        $freshArticle = $article->fresh();
        $likeCount = $freshArticle !== null ? $freshArticle->like_count : 0;

        return response()->json([
            'success' => true,
            'message' => $result['liked'] ? 'Artigo curtido com sucesso.' : 'Curtida removida com sucesso.',
            'data' => [
                'liked' => $result['liked'],
                'like_count' => $likeCount,
            ],
        ], JsonResponse::HTTP_OK);
    }

    /**
     * Check if user has liked an article.
     */
    public function check(Article $article): JsonResponse
    {
        /** @var string $userId */
        $userId = Auth::id();
        /** @var string $articleId */
        $articleId = $article->id;

        $hasLiked = $this->likeService->hasUserLiked($articleId, $userId);

        return response()->json([
            'success' => true,
            'data' => [
                'has_liked' => $hasLiked,
            ],
        ], JsonResponse::HTTP_OK);
    }
}
