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
     * @return LengthAwarePaginator<int, Article>
     */
    public function getPopularArticles(int $perPage = 15): LengthAwarePaginator
    {
        return Article::where('status', 'published')
            ->with('author:_id,name,username,avatar_url')
            ->orderByRaw('(view_count * 0.4 + like_count * 0.4 + comment_count * 0.2) DESC')
            ->orderBy('published_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get personalized articles based on following and popularity.
     *
     * @param  array<int, string>  $followingIds
     * @return LengthAwarePaginator<int, Article>
     */
    public function getPersonalizedArticles(array $followingIds, int $perPage = 15): LengthAwarePaginator
    {
        $query = Article::where('status', 'published')
            ->with('author:_id,name,username,avatar_url');

        if (count($followingIds) > 0) {
            $query->orderByRaw(
                'CASE WHEN author_id IN (?) THEN 1000 ELSE 0 END + (view_count * 0.4 + like_count * 0.4 + comment_count * 0.2) DESC',
                [$followingIds]
            );
        } else {
            $query->orderByRaw('(view_count * 0.4 + like_count * 0.4 + comment_count * 0.2) DESC');
        }

        return $query->orderBy('published_at', 'desc')->paginate($perPage);
    }
}
