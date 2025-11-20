<?php

namespace App\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Interface for Feed Service.
 *
 * Defines the contract for feed operations.
 */
interface FeedServiceInterface
{
    /**
     * Get public feed (for visitors).
     * Returns most popular articles.
     *
     * @return LengthAwarePaginator<int, \App\Models\Article>
     */
    public function getPublicFeed(int $perPage = 15): LengthAwarePaginator;

    /**
     * Get personalized feed for authenticated users.
     * Includes articles from followed users and recommendations.
     *
     * @return LengthAwarePaginator<int, \App\Models\Article>
     */
    public function getPersonalizedFeed(string $userId, int $perPage = 15): LengthAwarePaginator;
}
