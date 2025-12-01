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
     *
     * @param  CreateCommentDTO  $dto  The comment creation data
     * @return Comment The created comment
     */
    public function createComment(CreateCommentDTO $dto): Comment;

    /**
     * Update an existing comment.
     *
     * @param  Comment  $comment  The comment to update
     * @param  UpdateCommentDTO  $dto  The comment update data
     * @return Comment The updated comment
     */
    public function updateComment(Comment $comment, UpdateCommentDTO $dto): Comment;

    /**
     * Delete a comment.
     *
     * @param  Comment  $comment  The comment to delete
     * @return bool True if deleted successfully
     */
    public function deleteComment(Comment $comment): bool;

    /**
     * Get all comments for an article.
     *
     * @param  string  $articleId  The article ID to get comments for
     * @return Collection<int, Comment> The collection of comments
     */
    public function getCommentsByArticle(string $articleId): Collection;

    /**
     * Find a comment by ID.
     *
     * @param  string  $id  The comment ID to find
     * @return Comment|null The found comment or null
     */
    public function findCommentById(string $id): ?Comment;
}
