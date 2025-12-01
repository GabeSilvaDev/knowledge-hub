<?php

namespace App\Repositories;

use App\Contracts\FeedRepositoryInterface;
use App\Models\Article;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Feed Repository.
 *
 * Handles data access operations for feed.
 */
final readonly class FeedRepository implements FeedRepositoryInterface
{
    /**
     * Get popular articles for public feed.
     *
     * @param  int  $perPage  The number of articles per page
     * @return LengthAwarePaginator<int, Article> The paginated articles
     */
    public function getPopularArticles(int $perPage = 15): LengthAwarePaginator
    {
        return Article::where('status', 'published')
            ->with('author:_id,name,username,avatar_url')
            ->orderBy('view_count', 'desc')
            ->orderBy('like_count', 'desc')
            ->orderBy('published_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get personalized articles based on following and popularity.
     *
     * @param  array<int, string>  $followingIds  The IDs of users being followed
     * @param  int  $perPage  The number of articles per page
     * @return LengthAwarePaginator<int, Article> The paginated articles
     */
    public function getPersonalizedArticles(array $followingIds, int $perPage = 15): LengthAwarePaginator
    {
        $query = Article::where('status', 'published')
            ->with('author:_id,name,username,avatar_url');

        if (count($followingIds) > 0) {
            $query->whereIn('author_id', $followingIds);
        }

        $query->orderBy('view_count', 'desc')
            ->orderBy('like_count', 'desc')
            ->orderBy('published_at', 'desc');

        return $query->paginate($perPage);
    }
}
