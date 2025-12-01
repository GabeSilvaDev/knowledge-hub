<?php

namespace App\Repositories;

use App\Contracts\CommentRepositoryInterface;
use App\Models\Comment;
use Illuminate\Database\Eloquent\Collection;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * Comment Repository.
 *
 * Handles data access operations for comments.
 */
final readonly class CommentRepository implements CommentRepositoryInterface
{
    /**
     * Create a new comment.
     *
     * @param  array<string, mixed>  $data  The comment data
     * @return Comment The created comment
     */
    public function create(array $data): Comment
    {
        return Comment::create($data);
    }

    /**
     * Find a comment by ID.
     *
     * @param  string  $id  The comment ID
     * @return Comment|null The found comment or null
     */
    public function findById(string $id): ?Comment
    {
        return Comment::find($id);
    }

    /**
     * Update a comment.
     *
     * @param  Comment  $comment  The comment to update
     * @param  array<string, mixed>  $data  The update data
     * @return Comment The updated comment
     */
    public function update(Comment $comment, array $data): Comment
    {
        $comment->update($data);

        return $comment->fresh() ?? $comment;
    }

    /**
     * Delete a comment.
     *
     * @param  Comment  $comment  The comment to delete
     * @return bool True if deleted successfully
     */
    public function delete(Comment $comment): bool
    {
        $result = $comment->delete();

        return $result !== null && $result !== false;
    }

    /**
     * Get all comments for an article.
     *
     * @param  string  $articleId  The article ID to get comments for
     * @return Collection<int, Comment> The collection of comments
     */
    public function getByArticleId(string $articleId): Collection
    {
        return Comment::where('article_id', $articleId)
            ->with('user:_id,name,username,avatar_url')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get query builder for comments.
     *
     * @return QueryBuilder<Comment>
     */
    public function query(): QueryBuilder
    {
        return QueryBuilder::for(Comment::class)
            ->allowedFilters([
                AllowedFilter::exact('article_id'),
                AllowedFilter::exact('user_id'),
                AllowedFilter::partial('content'),
            ])
            ->allowedSorts(['created_at', 'updated_at'])
            ->allowedIncludes(['user', 'article'])
            ->defaultSort('-created_at');
    }
}
