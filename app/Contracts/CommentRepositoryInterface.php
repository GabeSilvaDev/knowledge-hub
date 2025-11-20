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
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Comment;

    /**
     * Find a comment by ID.
     */
    public function findById(string $id): ?Comment;

    /**
     * Update a comment.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Comment $comment, array $data): Comment;

    /**
     * Delete a comment.
     */
    public function delete(Comment $comment): bool;

    /**
     * Get all comments for an article.
     *
     * @return Collection<int, Comment>
     */
    public function getByArticleId(string $articleId): Collection;

    /**
     * Get query builder for comments.
     *
     * @return QueryBuilder<Comment>
     */
    public function query(): QueryBuilder;
}
