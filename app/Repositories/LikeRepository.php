<?php

namespace App\Repositories;

use App\Contracts\LikeRepositoryInterface;
use App\Models\Like;

/**
 * Like Repository.
 *
 * Handles data access operations for likes.
 */
final readonly class LikeRepository implements LikeRepositoryInterface
{
    /**
     * Toggle a like for a user on an article.
     *
     * @param  string  $articleId  The article ID to toggle like on
     * @param  string  $userId  The user ID who is liking/unliking
     * @return array{liked: bool, like: Like|null} The like status and like model
     */
    public function toggle(string $articleId, string $userId): array
    {
        $like = $this->findByArticleAndUser($articleId, $userId);

        if ($like instanceof \App\Models\Like) {
            $like->delete();

            return ['liked' => false, 'like' => null];
        }

        $newLike = Like::create([
            'article_id' => $articleId,
            'user_id' => $userId,
        ]);

        return ['liked' => true, 'like' => $newLike];
    }

    /**
     * Check if a user has liked an article.
     *
     * @param  string  $articleId  The article ID to check
     * @param  string  $userId  The user ID to check
     * @return bool True if the user has liked the article
     */
    public function hasLiked(string $articleId, string $userId): bool
    {
        return Like::where('article_id', $articleId)
            ->where('user_id', $userId)
            ->exists();
    }

    /**
     * Get like count for an article.
     *
     * @param  string  $articleId  The article ID to get like count for
     * @return int The number of likes
     */
    public function getCountByArticle(string $articleId): int
    {
        return Like::where('article_id', $articleId)->count();
    }

    /**
     * Find a like by article and user.
     *
     * @param  string  $articleId  The article ID
     * @param  string  $userId  The user ID
     * @return Like|null The found like or null
     */
    public function findByArticleAndUser(string $articleId, string $userId): ?Like
    {
        return Like::where('article_id', $articleId)
            ->where('user_id', $userId)
            ->first();
    }
}
