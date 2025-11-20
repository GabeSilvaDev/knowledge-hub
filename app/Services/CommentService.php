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
     */
    public function createComment(CreateCommentDTO $dto): Comment
    {
        return $this->commentRepository->create($dto->toArray());
    }

    /**
     * Update an existing comment.
     */
    public function updateComment(Comment $comment, UpdateCommentDTO $dto): Comment
    {
        return $this->commentRepository->update($comment, $dto->toArray());
    }

    /**
     * Delete a comment.
     */
    public function deleteComment(Comment $comment): bool
    {
        return $this->commentRepository->delete($comment);
    }

    /**
     * Get all comments for an article.
     *
     * @return Collection<int, Comment>
     */
    public function getCommentsByArticle(string $articleId): Collection
    {
        return $this->commentRepository->getByArticleId($articleId);
    }

    /**
     * Find a comment by ID.
     */
    public function findCommentById(string $id): ?Comment
    {
        return $this->commentRepository->findById($id);
    }
}
