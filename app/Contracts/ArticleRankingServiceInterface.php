<?php

namespace App\Contracts;

use Illuminate\Support\Collection;

interface ArticleRankingServiceInterface
{
    /**
     * Increment article view count in ranking.
     */
    public function incrementView(string $articleId, int $increment = 1): void;

    /**
     * Get top ranked articles by views.
     *
     * @return Collection<int, array{article_id: string, score: float}>
     */
    public function getTopArticles(int $limit = 10): Collection;

    /**
     * Get article rank position (1-based).
     */
    public function getArticleRank(string $articleId): ?int;

    /**
     * Get article score (total views).
     */
    public function getArticleScore(string $articleId): float;

    /**
     * Remove article from ranking.
     */
    public function removeArticle(string $articleId): void;

    /**
     * Reset entire ranking (clear all scores).
     */
    public function resetRanking(): void;

    /**
     * Sync MongoDB view counts to Redis ranking.
     */
    public function syncFromDatabase(): void;

    /**
     * Get ranking statistics.
     *
     * @return array{total_articles: int, total_views: float, top_score: float}
     */
    public function getStatistics(): array;
}
