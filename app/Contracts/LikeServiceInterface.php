<?php

namespace App\Contracts;

use App\Models\Like;

/**
 * Interface for Like Service.
 *
 * Defines the contract for like business logic operations.
 */
interface LikeServiceInterface
{
    /**
     * Toggle a like for a user on an article.
     *
     * @return array{liked: bool, like: Like|null}
     */
    public function toggleLike(string $articleId, string $userId): array;

    /**
     * Check if a user has liked an article.
     */
    public function hasUserLiked(string $articleId, string $userId): bool;
}
