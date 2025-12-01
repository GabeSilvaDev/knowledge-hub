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
                ? 'Nenhuma recomendação de usuário disponível no momento.'
                : 'Usuários recomendados com base em seguidores em comum.',
            'data' => $recommendations->toArray(),
        ]);
    }

    /**
     * Get recommended articles for the authenticated user.
     *
     * Returns articles based on tags and categories the user interacts with.
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
                ? 'Nenhuma recomendação de artigo disponível no momento.'
                : 'Artigos recomendados com base em seus interesses.',
            'data' => $recommendations->toArray(),
        ]);
    }

    /**
     * Get related articles for a specific article.
     *
     * Returns articles with similar tags and categories.
     */
    public function related(Request $request, string $articleId): JsonResponse
    {
        /** @var int $limit */
        $limit = (int) $request->query('limit', 10);

        $recommendations = $this->recommendationService->getRelatedArticles($articleId, $limit);

        return response()->json([
            'success' => true,
            'message' => $recommendations->isEmpty()
                ? 'Nenhum artigo relacionado encontrado.'
                : 'Artigos relacionados por tags e categorias.',
            'data' => $recommendations->toArray(),
        ]);
    }

    /**
     * Get recommended authors.
     *
     * Returns influential authors based on follower network.
     */
    public function authors(Request $request): JsonResponse
    {
        /** @var int $limit */
        $limit = (int) $request->query('limit', 10);

        $recommendations = $this->recommendationService->getRecommendedAuthors($limit);

        return response()->json([
            'success' => true,
            'message' => $recommendations->isEmpty()
                ? 'Nenhum autor influente encontrado no momento.'
                : 'Autores influentes na plataforma.',
            'data' => $recommendations->toArray(),
        ]);
    }

    /**
     * Get topics of interest for the authenticated user.
     *
     * Returns topics/tags based on user's likes and interactions.
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
                ? 'Nenhum tópico de interesse identificado ainda.'
                : 'Tópicos baseados em suas interações.',
            'data' => $recommendations->toArray(),
        ]);
    }

    /**
     * Sync data from MongoDB to Neo4j.
     *
     * Admin-only endpoint to synchronize graph data.
     */
    public function sync(): JsonResponse
    {
        $stats = $this->recommendationService->syncFromDatabase();

        return response()->json([
            'success' => true,
            'message' => 'Sincronização com Neo4j concluída com sucesso.',
            'data' => [
                'synced' => $stats,
                'neo4j_available' => $this->recommendationService->isAvailable(),
            ],
        ]);
    }

    /**
     * Get Neo4j graph statistics.
     */
    public function statistics(): JsonResponse
    {
        $stats = $this->recommendationService->getStatistics();
        $isAvailable = $this->recommendationService->isAvailable();

        return response()->json([
            'success' => true,
            'message' => $isAvailable
                ? 'Estatísticas do grafo de recomendações.'
                : 'Neo4j não está disponível no momento.',
            'data' => [
                'neo4j_available' => $isAvailable,
                'statistics' => $stats,
            ],
        ]);
    }
}
