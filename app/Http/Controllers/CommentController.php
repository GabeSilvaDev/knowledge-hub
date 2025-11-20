<?php

namespace App\Http\Controllers;

use App\Contracts\CommentServiceInterface;
use App\DTOs\CreateCommentDTO;
use App\DTOs\UpdateCommentDTO;
use App\Http\Requests\StoreCommentRequest;
use App\Http\Requests\UpdateCommentRequest;
use App\Models\Comment;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

/**
 * Comment Controller.
 *
 * Handles HTTP requests for comment operations.
 */
final class CommentController extends Controller
{
    public function __construct(
        private readonly CommentServiceInterface $commentService,
    ) {}

    /**
     * Get all comments for an article.
     */
    public function index(string $articleId): JsonResponse
    {
        $comments = $this->commentService->getCommentsByArticle($articleId);

        return response()->json([
            'success' => true,
            'data' => $comments,
        ]);
    }

    /**
     * Store a new comment.
     */
    public function store(StoreCommentRequest $request): JsonResponse
    {
        $dto = CreateCommentDTO::fromArray([
            ...$request->validated(),
            'user_id' => Auth::id(),
        ]);

        $comment = $this->commentService->createComment($dto);

        return response()->json([
            'success' => true,
            'message' => 'Comentário criado com sucesso.',
            'data' => $comment->load('user:_id,name,username,avatar_url'),
        ], JsonResponse::HTTP_CREATED);
    }

    /**
     * Update an existing comment.
     */
    public function update(UpdateCommentRequest $request, Comment $comment): JsonResponse
    {
        if ($comment->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Você não tem permissão para editar este comentário.',
            ], JsonResponse::HTTP_FORBIDDEN);
        }

        $dto = UpdateCommentDTO::fromArray($request->validated());
        $updatedComment = $this->commentService->updateComment($comment, $dto);

        return response()->json([
            'success' => true,
            'message' => 'Comentário atualizado com sucesso.',
            'data' => $updatedComment->load('user:_id,name,username,avatar_url'),
        ], JsonResponse::HTTP_OK);
    }

    /**
     * Delete a comment.
     */
    public function destroy(Comment $comment): JsonResponse
    {
        if ($comment->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Você não tem permissão para excluir este comentário.',
            ], JsonResponse::HTTP_FORBIDDEN);
        }

        $this->commentService->deleteComment($comment);

        return response()->json([
            'success' => true,
            'message' => 'Comentário excluído com sucesso.',
        ], JsonResponse::HTTP_OK);
    }
}
