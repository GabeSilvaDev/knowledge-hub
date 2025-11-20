<?php

namespace App\Http\Controllers;

use App\Contracts\LikeServiceInterface;
use App\Models\Article;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

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
        $userId = Auth::id();

        if ($userId === null) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não autenticado.',
            ], JsonResponse::HTTP_UNAUTHORIZED);
        }

        if (! is_string($userId)) {
            throw new RuntimeException('User ID must be a string');
        }

        if (! is_string($article->id)) {
            throw new RuntimeException('Article ID must be a string');
        }

        $result = $this->likeService->toggleLike($article->id, $userId);

        $freshArticle = $article->fresh();

        if ($freshArticle === null) {
            throw new RuntimeException('Failed to refresh article');
        }

        return response()->json([
            'success' => true,
            'message' => $result['liked'] ? 'Artigo curtido com sucesso.' : 'Curtida removida com sucesso.',
            'data' => [
                'liked' => $result['liked'],
                'like_count' => $freshArticle->like_count,
            ],
        ], JsonResponse::HTTP_OK);
    }

    /**
     * Check if user has liked an article.
     */
    public function check(Article $article): JsonResponse
    {
        $userId = Auth::id();

        if ($userId === null) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não autenticado.',
            ], JsonResponse::HTTP_UNAUTHORIZED);
        }

        if (! is_string($userId)) {
            throw new RuntimeException('User ID must be a string');
        }

        if (! is_string($article->id)) {
            throw new RuntimeException('Article ID must be a string');
        }

        $hasLiked = $this->likeService->hasUserLiked($article->id, $userId);

        return response()->json([
            'success' => true,
            'data' => [
                'has_liked' => $hasLiked,
            ],
        ], JsonResponse::HTTP_OK);
    }
}
