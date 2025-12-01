<?php

namespace App\Services;

use App\Contracts\LikeRepositoryInterface;
use App\Contracts\LikeServiceInterface;
use App\Models\Like;

/**
 * Like Service.
 *
 * Handles business logic for like operations.
 */
final readonly class LikeService implements LikeServiceInterface
{
    public function __construct(
        private LikeRepositoryInterface $likeRepository,
    ) {}

    /**
     * Toggle a like for a user on an article.
     *
     * @param  string  $articleId  The article ID to toggle like on
     * @param  string  $userId  The user ID who is liking/unliking
     * @return array{liked: bool, like: Like|null} The like status and like model
     */
    public function toggleLike(string $articleId, string $userId): array
    {
        return $this->likeRepository->toggle($articleId, $userId);
    }

    /**
     * Check if a user has liked an article.
     *
     * @param  string  $articleId  The article ID to check
     * @param  string  $userId  The user ID to check
     * @return bool True if the user has liked the article
     */
    public function hasUserLiked(string $articleId, string $userId): bool
    {
        return $this->likeRepository->hasLiked($articleId, $userId);
    }
}
