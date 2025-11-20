<?php

namespace App\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Interface for Feed Repository.
 *
 * Defines the contract for feed data access operations.
 */
interface FeedRepositoryInterface
{
    /**
     * Get popular articles for public feed.
     *
     * @return LengthAwarePaginator<int, \App\Models\Article>
     */
    public function getPopularArticles(int $perPage = 15): LengthAwarePaginator;

    /**
     * Get personalized articles based on following and popularity.
     *
     * @param  array<int, string>  $followingIds
     * @return LengthAwarePaginator<int, \App\Models\Article>
     */
    public function getPersonalizedArticles(array $followingIds, int $perPage = 15): LengthAwarePaginator;
}
