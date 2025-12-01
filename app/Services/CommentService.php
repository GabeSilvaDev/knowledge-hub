<?php

namespace App\Services;

use App\Contracts\CommentRepositoryInterface;
use App\Contracts\CommentServiceInterface;
use App\DTOs\CreateCommentDTO;
use App\DTOs\UpdateCommentDTO;
use App\Models\Comment;
use Illuminate\Database\Eloquent\Collection;

/**
 * Comment Service.
 *
 * Handles business logic for comment operations.
 */
final readonly class CommentService implements CommentServiceInterface
{
    public function __construct(
        private CommentRepositoryInterface $commentRepository,
    ) {}

    /**
     * Create a new comment.
     *
     * @param  CreateCommentDTO  $dto  The comment creation data
     * @return Comment The created comment
     */
    public function createComment(CreateCommentDTO $dto): Comment
    {
        return $this->commentRepository->create($dto->toArray());
    }

    /**
     * Update an existing comment.
     *
     * @param  Comment  $comment  The comment to update
     * @param  UpdateCommentDTO  $dto  The update data
     * @return Comment The updated comment
     */
    public function updateComment(Comment $comment, UpdateCommentDTO $dto): Comment
    {
        return $this->commentRepository->update($comment, $dto->toArray());
    }

    /**
     * Delete a comment.
     *
     * @param  Comment  $comment  The comment to delete
     * @return bool True if deleted successfully
     */
    public function deleteComment(Comment $comment): bool
    {
        return $this->commentRepository->delete($comment);
    }

    /**
     * Get all comments for an article.
     *
     * @param  string  $articleId  The article ID to get comments for
     * @return Collection<int, Comment> The collection of comments
     */
    public function getCommentsByArticle(string $articleId): Collection
    {
        return $this->commentRepository->getByArticleId($articleId);
    }

    /**
     * Find a comment by ID.
     *
     * @param  string  $id  The comment ID to find
     * @return Comment|null The found comment or null
     */
    public function findCommentById(string $id): ?Comment
    {
        return $this->commentRepository->findById($id);
    }
}
