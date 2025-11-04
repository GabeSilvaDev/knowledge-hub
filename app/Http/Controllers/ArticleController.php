<?php

namespace App\Http\Controllers;

use App\DTOs\CreateArticleDTO;
use App\Http\Requests\StoreArticleRequest;
use App\Http\Requests\UpdateArticleRequest;
use App\Models\Article;
use App\Services\ArticleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ArticleController extends Controller
{
    public function __construct(
        private readonly ArticleService $articleService
    ) {}

    /**
     * Display a listing of articles.
     */
    public function index(): JsonResponse
    {
        $articles = $this->articleService
            ->query()
            ->paginate();

        return response()->json($articles, JsonResponse::HTTP_OK);
    }

    /**
     * Store a newly created article.
     */
    public function store(StoreArticleRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['author_id'] = auth()->id();

        $dto = CreateArticleDTO::fromArray($data);
        $article = $this->articleService->createArticle($dto);

        return response()->json([
            'message' => 'Artigo criado com sucesso.',
            'data' => $article,
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified article.
     */
    public function show(Article $article): JsonResponse
    {
        return response()->json([
            'data' => $article->load('author'),
        ], JsonResponse::HTTP_OK);
    }

    /**
     * Update the specified article.
     */
    public function update(UpdateArticleRequest $request, Article $article): JsonResponse
    {
        $data = $request->validated();
        $updatedArticle = $this->articleService->updateArticle($article, $data);

        return response()->json([
            'message' => 'Artigo atualizado com sucesso.',
            'data' => $updatedArticle,
        ], JsonResponse::HTTP_OK);
    }

    /**
     * Remove the specified article.
     */
    public function destroy(Article $article): JsonResponse
    {
        $this->articleService->deleteArticle($article);

        return response()->json([
            'message' => 'Artigo excluído com sucesso.',
        ], JsonResponse::HTTP_OK);
    }
}
