<?php

namespace App\Contracts;

use Illuminate\Support\Collection;

/**
 * Article ranking service contract.
 *
 * Defines the interface for article view tracking and ranking operations using Redis.
 */
interface ArticleRankingServiceInterface
{
    /**
     * Increment article view count in ranking.
     *
     * Increases the view count for an article in the Redis sorted set.
     *
     * @param  string  $articleId  The article ID
     * @param  int  $increment  Number of views to add (default: 1)
     */
    public function incrementView(string $articleId, int $increment = 1): void;

    /**
     * Get top ranked articles by views.
     *
     * Retrieves the highest viewed articles from Redis ranking.
     *
     * @param  int  $limit  Maximum number of articles (default: 10)
     * @return Collection<int, array{article_id: string, score: float}> Top articles
     */
    public function getTopArticles(int $limit = 10): Collection;

    /**
     * Get article rank position (1-based).
     *
     * Returns the ranking position of the article in the leaderboard.
     *
     * @param  string  $articleId  The article ID
     * @return int|null The rank (1-based) or null if not ranked
     */
    public function getArticleRank(string $articleId): ?int;

    /**
     * Get article score (total views).
     *
     * Returns the total view count for the article from Redis.
     *
     * @param  string  $articleId  The article ID
     * @return float The view count score
     */
    public function getArticleScore(string $articleId): float;

    /**
     * Remove article from ranking.
     *
     * Deletes the article entry from the Redis sorted set.
     *
     * @param  string  $articleId  The article ID to remove
     */
    public function removeArticle(string $articleId): void;

    /**
     * Reset entire ranking (clear all scores).
     *
     * Deletes all entries from the Redis ranking.
     */
    public function resetRanking(): void;

    /**
     * Sync MongoDB view counts to Redis ranking.
     *
     * Rebuilds Redis ranking from database view counts.
     */
    public function syncFromDatabase(): void;

    /**
     * Get ranking statistics.
     *
     * Returns aggregate statistics about the ranking system.
     *
     * @return array{total_articles: int, total_views: float, top_score: float} Statistics
     */
    public function getStatistics(): array;

    /**
     * Get enriched top articles with article details.
     *
     * Retrieves top articles and enriches with full article data.
     *
     * @param  int  $limit  Maximum number of articles (default: 10)
     * @return Collection<int, array{rank: int, article_id: string, views: int, article: array<string, mixed>|null}> Enriched ranking
     */
    public function getEnrichedTopArticles(int $limit = 10): Collection;

    /**
     * Get enriched article ranking info.
     *
     * Retrieves ranking info for specific article with article data.
     *
     * @param  string  $articleId  The article ID
     * @return array{article_id: string, rank: int|null, views: int, article: array<string, mixed>} Enriched ranking data
     */
    public function getEnrichedArticleRanking(string $articleId): array;
}
