<?php

namespace App\Repositories;

use App\Contracts\CommentRepositoryInterface;
use App\Models\Comment;
use Illuminate\Database\Eloquent\Collection;
use RuntimeException;
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
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Comment
    {
        return Comment::create($data);
    }

    /**
     * Find a comment by ID.
     */
    public function findById(string $id): ?Comment
    {
        return Comment::find($id);
    }

    /**
     * Update a comment.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Comment $comment, array $data): Comment
    {
        $comment->update($data);

        $fresh = $comment->fresh();

        if ($fresh === null) {
            throw new RuntimeException('Failed to refresh comment after update');
        }

        return $fresh;
    }

    /**
     * Delete a comment.
     */
    public function delete(Comment $comment): bool
    {
        $result = $comment->delete();

        return $result !== null && $result !== false;
    }

    /**
     * Get all comments for an article.
     *
     * @return Collection<int, Comment>
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
