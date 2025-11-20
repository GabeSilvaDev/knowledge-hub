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
     * @return array{liked: bool, like: Like|null}
     */
    public function toggleLike(string $articleId, string $userId): array
    {
        return $this->likeRepository->toggle($articleId, $userId);
    }

    /**
     * Check if a user has liked an article.
     */
    public function hasUserLiked(string $articleId, string $userId): bool
    {
        return $this->likeRepository->hasLiked($articleId, $userId);
    }
}
