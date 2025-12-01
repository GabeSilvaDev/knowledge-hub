<?php

declare(strict_types=1);

namespace App\Contracts;

use Illuminate\Support\LazyCollection;

/**
 * Interface for Sync Repository.
 *
 * Defines the contract for data synchronization operations between MongoDB and Neo4j.
 */
interface SyncRepositoryInterface
{
    /**
     * Get all users as a lazy collection for sync.
     *
     * @return LazyCollection<int, array{id: string, name: string, email: string, username: string}> The users data
     */
    public function getAllUsersForSync(): LazyCollection;

    /**
     * Get all published articles as a lazy collection for sync.
     *
     * @return LazyCollection<int, array{id: string, title: string, slug: string, status: string, author_id: string, view_count: int, like_count: int, tags: list<string>, categories: list<string>}> The articles data
     */
    public function getAllPublishedArticlesForSync(): LazyCollection;

    /**
     * Get all followers as a lazy collection for sync.
     *
     * @return LazyCollection<int, array{follower_id: string, following_id: string}> The followers data
     */
    public function getAllFollowersForSync(): LazyCollection;

    /**
     * Get all likes as a lazy collection for sync.
     *
     * @return LazyCollection<int, array{user_id: string, article_id: string}> The likes data
     */
    public function getAllLikesForSync(): LazyCollection;
}
