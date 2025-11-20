<?php

namespace App\Contracts;

use App\DTOs\CreateCommentDTO;
use App\DTOs\UpdateCommentDTO;
use App\Models\Comment;
use Illuminate\Database\Eloquent\Collection;

/**
 * Interface for Comment Service.
 *
 * Defines the contract for comment business logic operations.
 */
interface CommentServiceInterface
{
    /**
     * Create a new comment.
     */
    public function createComment(CreateCommentDTO $dto): Comment;

    /**
     * Update an existing comment.
     */
    public function updateComment(Comment $comment, UpdateCommentDTO $dto): Comment;

    /**
     * Delete a comment.
     */
    public function deleteComment(Comment $comment): bool;

    /**
     * Get all comments for an article.
     *
     * @return Collection<int, Comment>
     */
    public function getCommentsByArticle(string $articleId): Collection;

    /**
     * Find a comment by ID.
     */
    public function findCommentById(string $id): ?Comment;
}
