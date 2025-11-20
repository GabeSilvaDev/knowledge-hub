<?php

namespace App\Contracts;

use App\Models\Like;

/**
 * Interface for Like Repository.
 *
 * Defines the contract for like data access operations.
 */
interface LikeRepositoryInterface
{
    /**
     * Toggle a like for a user on an article.
     * If like exists, delete it. If not, create it.
     *
     * @return array{liked: bool, like: Like|null}
     */
    public function toggle(string $articleId, string $userId): array;

    /**
     * Check if a user has liked an article.
     */
    public function hasLiked(string $articleId, string $userId): bool;

    /**
     * Get like count for an article.
     */
    public function getCountByArticle(string $articleId): int;

    /**
     * Find a like by article and user.
     */
    public function findByArticleAndUser(string $articleId, string $userId): ?Like;
}
