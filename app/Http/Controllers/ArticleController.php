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
    /**
     * ArticleController constructor.
     *
     * Initializes the controller with article service dependency.
     *
     * @param  ArticleService  $articleService  Service for handling article operations
     */
    public function __construct(
        private readonly ArticleService $articleService
    ) {}

    /**
     * Display a listing of articles.
     *
     * Returns a paginated list of articles with filtering and sorting capabilities.
     *
     * @return JsonResponse Paginated list of articles
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
     *
     * Creates a new article with the authenticated user as the author.
     *
     * @param  StoreArticleRequest  $request  Validated article creation request
     * @return JsonResponse Created article with success message
     */
    public function store(StoreArticleRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['author_id'] = auth()->id();

        $dto = CreateArticleDTO::fromArray($data);
        $article = $this->articleService->createArticle($dto);

        return response()->json([
            'message' => 'Article created successfully.',
            'data' => $article,
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified article.
     *
     * Returns the article with its relationships loaded.
     *
     * @param  Article  $article  The article to display (route model binding)
     * @return JsonResponse Article with loaded relationships
     */
    public function show(Article $article): JsonResponse
    {
        $article = $this->articleService->loadArticleRelationships($article);

        return response()->json([
            'data' => $article,
        ], JsonResponse::HTTP_OK);
    }

    /**
     * Update the specified article.
     *
     * Updates an existing article with validated data.
     *
     * @param  UpdateArticleRequest  $request  Validated article update request
     * @param  Article  $article  The article to update (route model binding)
     * @return JsonResponse Updated article with success message
     */
    public function update(UpdateArticleRequest $request, Article $article): JsonResponse
    {
        $data = $request->validated();
        $updatedArticle = $this->articleService->updateArticle($article, $data);

        return response()->json([
            'message' => 'Article updated successfully.',
            'data' => $updatedArticle,
        ], JsonResponse::HTTP_OK);
    }

    /**
     * Remove the specified article.
     *
     * Soft deletes the specified article from the database.
     *
     * @param  Article  $article  The article to delete (route model binding)
     * @return JsonResponse Success message
     */
    public function destroy(Article $article): JsonResponse
    {
        $this->articleService->deleteArticle($article);

        return response()->json([
            'message' => 'Article deleted successfully.',
        ], JsonResponse::HTTP_OK);
    }

    /**
     * Get popular articles.
     *
     * Returns a list of the most popular articles based on view count
     * within a specified time period.
     *
     * @return JsonResponse List of popular articles
     */
    public function popular(): JsonResponse
    {
        $limit = (int) request()->query('limit', 10);
        $days = (int) request()->query('days', 30);

        $articles = $this->articleService->getPopularArticles($limit, $days);

        return response()->json([
            'data' => $articles,
        ], JsonResponse::HTTP_OK);
    }
}
