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
     *
     * @param  string  $articleId  The article ID to get comments for
     * @return JsonResponse The list of comments
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
     *
     * @param  StoreCommentRequest  $request  The validated comment request
     * @return JsonResponse The created comment with user data
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
            'message' => 'Comment created successfully.',
            'data' => $comment->load('user:_id,name,username,avatar_url'),
        ], JsonResponse::HTTP_CREATED);
    }

    /**
     * Update an existing comment.
     *
     * @param  UpdateCommentRequest  $request  The validated update request
     * @param  Comment  $comment  The comment to update
     * @return JsonResponse The updated comment or forbidden error
     */
    public function update(UpdateCommentRequest $request, Comment $comment): JsonResponse
    {
        if ($comment->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to edit this comment.',
            ], JsonResponse::HTTP_FORBIDDEN);
        }

        $dto = UpdateCommentDTO::fromArray($request->validated());
        $updatedComment = $this->commentService->updateComment($comment, $dto);

        return response()->json([
            'success' => true,
            'message' => 'Comment updated successfully.',
            'data' => $updatedComment->load('user:_id,name,username,avatar_url'),
        ], JsonResponse::HTTP_OK);
    }

    /**
     * Delete a comment.
     *
     * @param  Comment  $comment  The comment to delete
     * @return JsonResponse The deletion result or forbidden error
     */
    public function destroy(Comment $comment): JsonResponse
    {
        if ($comment->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to delete this comment.',
            ], JsonResponse::HTTP_FORBIDDEN);
        }

        $this->commentService->deleteComment($comment);

        return response()->json([
            'success' => true,
            'message' => 'Comment deleted successfully.',
        ], JsonResponse::HTTP_OK);
    }
}
