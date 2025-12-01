<?php

namespace App\Contracts;

use App\DTOs\UserRankingDTO;
use Illuminate\Support\Collection;

/**
 * User ranking service contract.
 *
 * Defines the interface for user influence tracking and ranking operations using Redis.
 * Implements RF-051: Ranking de Usuários Baseado em Influência.
 */
interface UserRankingServiceInterface
{
    /**
     * Calculate and update influence score for a user.
     *
     * Computes influence based on:
     * - Number of followers (weight: 2.0)
     * - Total article views (weight: 0.5)
     * - Total article likes (weight: 1.0)
     * - Total article comments (weight: 0.8)
     * - Number of published articles (weight: 1.5)
     *
     * @param  string  $userId  The user ID to calculate score for
     * @return float The calculated influence score
     */
    public function calculateInfluenceScore(string $userId): float;

    /**
     * Update influence score for a user in Redis.
     *
     * @param  string  $userId  The user ID
     * @param  float  $score  The influence score
     */
    public function updateScore(string $userId, float $score): void;

    /**
     * Increment user score by a specified amount.
     *
     * @param  string  $userId  The user ID
     * @param  float  $increment  Amount to increment (default: 1.0)
     */
    public function incrementScore(string $userId, float $increment = 1.0): void;

    /**
     * Get top ranked users by influence.
     *
     * Retrieves the highest influence users from Redis sorted set.
     *
     * @param  int  $limit  Maximum number of users to return (default: 10)
     * @return Collection<int, array{user_id: string, score: float}> Top users with scores
     */
    public function getTopUsers(int $limit = 10): Collection;

    /**
     * Get user rank position (1-based).
     *
     * Returns the ranking position of the user in the influence leaderboard.
     *
     * @param  string  $userId  The user ID
     * @return int|null The rank (1-based) or null if not ranked
     */
    public function getUserRank(string $userId): ?int;

    /**
     * Get user influence score.
     *
     * Returns the total influence score for the user from Redis.
     *
     * @param  string  $userId  The user ID
     * @return float The influence score
     */
    public function getUserScore(string $userId): float;

    /**
     * Remove user from ranking.
     *
     * Deletes the user entry from the Redis sorted set.
     *
     * @param  string  $userId  The user ID to remove
     */
    public function removeUser(string $userId): void;

    /**
     * Reset entire ranking (clear all scores).
     *
     * Deletes all entries from the Redis ranking.
     */
    public function resetRanking(): void;

    /**
     * Sync MongoDB user data to Redis ranking.
     *
     * Rebuilds Redis ranking from database user statistics.
     */
    public function syncFromDatabase(): void;

    /**
     * Get ranking statistics.
     *
     * Returns aggregate statistics about the ranking system.
     *
     * @return array{total_users: int, total_score: float, top_score: float, average_score: float} Statistics
     */
    public function getStatistics(): array;

    /**
     * Get enriched top users with user details.
     *
     * Retrieves top users and enriches with full user data.
     *
     * @param  int  $limit  Maximum number of users (default: 10)
     * @return Collection<int, array{rank: int, user_id: string, score: float, user: array<string, mixed>|null}> Enriched ranking
     */
    public function getEnrichedTopUsers(int $limit = 10): Collection;

    /**
     * Get enriched user ranking info.
     *
     * Retrieves ranking info for specific user with user data.
     *
     * @param  string  $userId  The user ID
     * @return UserRankingDTO User ranking data transfer object
     */
    public function getEnrichedUserRanking(string $userId): UserRankingDTO;

    /**
     * Recalculate and update ranking for a specific user.
     *
     * @param  string  $userId  The user ID to recalculate
     */
    public function recalculateUser(string $userId): void;
}
