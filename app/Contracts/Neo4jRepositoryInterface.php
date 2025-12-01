<?php

namespace App\Contracts;

use Illuminate\Support\Collection;

/**
 * Interface for Neo4j Repository.
 *
 * Defines the contract for Neo4j graph database operations.
 */
interface Neo4jRepositoryInterface
{
    /**
     * Check if Neo4j is connected.
     */
    public function isConnected(): bool;

    /**
     * Sync a user node to Neo4j.
     *
     * @param  array<string, mixed>  $userData  User data to sync
     */
    public function syncUser(array $userData): void;

    /**
     * Delete a user node from Neo4j.
     */
    public function deleteUser(string $userId): void;

    /**
     * Sync an article node to Neo4j.
     *
     * @param  array<string, mixed>  $articleData  Article data to sync
     */
    public function syncArticle(array $articleData): void;

    /**
     * Delete an article node from Neo4j.
     */
    public function deleteArticle(string $articleId): void;

    /**
     * Sync a follow relationship.
     */
    public function syncFollow(string $followerId, string $followingId): void;

    /**
     * Delete a follow relationship.
     */
    public function deleteFollow(string $followerId, string $followingId): void;

    /**
     * Sync a like relationship.
     */
    public function syncLike(string $userId, string $articleId): void;

    /**
     * Delete a like relationship.
     */
    public function deleteLike(string $userId, string $articleId): void;

    /**
     * Get users with common followers.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function getUsersWithCommonFollowers(string $userId, int $limit): Collection;

    /**
     * Get articles related by tags.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function getRelatedArticlesByTags(string $articleId, int $limit): Collection;

    /**
     * Get influential authors.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function getInfluentialAuthors(int $minFollowers, int $limit): Collection;

    /**
     * Get topics of interest for a user.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function getTopicsOfInterest(string $userId, int $limit): Collection;

    /**
     * Get recommended articles for a user.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function getRecommendedArticlesForUser(string $userId, int $limit): Collection;

    /**
     * Get statistics about the graph.
     *
     * @return array<string, int>
     */
    public function getStatistics(): array;

    /**
     * Clear all data from the graph.
     */
    public function clearAll(): void;
}
