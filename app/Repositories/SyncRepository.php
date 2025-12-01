<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\SyncRepositoryInterface;
use App\Models\Article;
use App\Models\Follower;
use App\Models\Like;
use App\Models\User;
use Illuminate\Support\LazyCollection;

/**
 * Sync Repository.
 *
 * Handles data access operations for synchronization between MongoDB and Neo4j.
 */
final readonly class SyncRepository implements SyncRepositoryInterface
{
    /**
     * Get all users as a lazy collection for sync.
     *
     * @return LazyCollection<int, array{id: string, name: string, email: string, username: string}> The users data
     */
    public function getAllUsersForSync(): LazyCollection
    {
        return User::query()->cursor()->map(fn(User $user): array => [
            'id' => (string) $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'username' => $user->username,
        ]);
    }

    /**
     * Get all published articles as a lazy collection for sync.
     *
     * @return LazyCollection<int, array{id: string, title: string, slug: string, status: string, author_id: string, view_count: int, like_count: int, tags: list<string>, categories: list<string>}> The articles data
     */
    public function getAllPublishedArticlesForSync(): LazyCollection
    {
        return Article::query()
            ->where('status', 'published')
            ->cursor()
            ->map(fn(Article $article): array => [
                'id' => (string) $article->id,
                'title' => $article->title,
                'slug' => $article->slug,
                'status' => $article->status,
                'author_id' => (string) $article->author_id,
                'view_count' => (int) $article->view_count,
                'like_count' => (int) $article->like_count,
                'tags' => array_values($article->tags ?? []),
                'categories' => array_values($article->categories ?? []),
            ]);
    }

    /**
     * Get all followers as a lazy collection for sync.
     *
     * @return LazyCollection<int, array{follower_id: string, following_id: string}> The followers data
     */
    public function getAllFollowersForSync(): LazyCollection
    {
        return Follower::query()->cursor()->map(fn(Follower $follower): array => [
            'follower_id' => (string) $follower->follower_id,
            'following_id' => (string) $follower->following_id,
        ]);
    }

    /**
     * Get all likes as a lazy collection for sync.
     *
     * @return LazyCollection<int, array{user_id: string, article_id: string}> The likes data
     */
    public function getAllLikesForSync(): LazyCollection
    {
        return Like::query()->cursor()->map(fn(Like $like): array => [
            'user_id' => (string) $like->user_id,
            'article_id' => (string) $like->article_id,
        ]);
    }
}
