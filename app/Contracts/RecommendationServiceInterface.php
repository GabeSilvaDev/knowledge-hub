<?php

namespace App\Contracts;

use App\DTOs\RecommendationDTO;

/**
 * Interface for Recommendation Service.
 *
 * Defines the contract for recommendation operations using Neo4j graph database.
 */
interface RecommendationServiceInterface
{
    /**
     * Get recommended users for a specific user.
     *
     * Returns users with common followers (similar social circles).
     */
    public function getRecommendedUsers(string $userId, int $limit = 10): RecommendationDTO;

    /**
     * Get recommended articles for a specific user.
     *
     * Returns articles based on tags and categories the user interacts with.
     */
    public function getRecommendedArticles(string $userId, int $limit = 10): RecommendationDTO;

    /**
     * Get related articles for a specific article.
     *
     * Returns articles with similar tags and categories.
     */
    public function getRelatedArticles(string $articleId, int $limit = 10): RecommendationDTO;

    /**
     * Get recommended authors.
     *
     * Returns influential authors based on follower network.
     */
    public function getRecommendedAuthors(int $limit = 10): RecommendationDTO;

    /**
     * Get topics of interest for a user.
     *
     * Returns topics/tags based on user's likes and interactions.
     */
    public function getTopicsOfInterest(string $userId, int $limit = 10): RecommendationDTO;

    /**
     * Sync all data from MongoDB to Neo4j.
     *
     * @return array<string, int> Statistics about the sync operation
     */
    public function syncFromDatabase(): array;

    /**
     * Check if Neo4j is connected and working.
     */
    public function isAvailable(): bool;

    /**
     * Get statistics about the recommendation graph.
     *
     * @return array<string, int>
     */
    public function getStatistics(): array;
}
