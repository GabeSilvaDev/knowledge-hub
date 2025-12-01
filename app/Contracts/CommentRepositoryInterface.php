<?php

namespace App\Contracts;

use App\Models\Comment;
use Illuminate\Database\Eloquent\Collection;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * Interface for Comment Repository.
 *
 * Defines the contract for comment data access operations.
 */
interface CommentRepositoryInterface
{
    /**
     * Create a new comment.
     *
     * @param  array<string, mixed>  $data  The comment data
     * @return Comment The created comment
     */
    public function create(array $data): Comment;

    /**
     * Find a comment by ID.
     *
     * @param  string  $id  The comment ID
     * @return Comment|null The found comment or null
     */
    public function findById(string $id): ?Comment;

    /**
     * Update a comment.
     *
     * @param  Comment  $comment  The comment to update
     * @param  array<string, mixed>  $data  The update data
     * @return Comment The updated comment
     */
    public function update(Comment $comment, array $data): Comment;

    /**
     * Delete a comment.
     *
     * @param  Comment  $comment  The comment to delete
     * @return bool True if deleted successfully
     */
    public function delete(Comment $comment): bool;

    /**
     * Get all comments for an article.
     *
     * @param  string  $articleId  The article ID to get comments for
     * @return Collection<int, Comment> The collection of comments
     */
    public function getByArticleId(string $articleId): Collection;

    /**
     * Get query builder for comments.
     *
     * @return QueryBuilder<Comment>
     */
    public function query(): QueryBuilder;
}
