<?php

namespace App\Services;

use App\Contracts\ArticleRankingServiceInterface;
use App\Contracts\ArticleRepositoryInterface;
use App\Models\Article;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Redis;

class ArticleRankingService implements ArticleRankingServiceInterface
{
    private const string RANKING_KEY = 'articles:ranking:views';

    private const int TTL_DAYS = 90;

    /**
     * Initialize the Article Ranking Service.
     *
     * Constructs the service with injected repository for database operations.
     *
     * @param  ArticleRepositoryInterface  $articleRepository  Repository for article data access
     */
    public function __construct(
        private readonly ArticleRepositoryInterface $articleRepository
    ) {}

    /**
     * Increment view count for an article.
     *
     * Updates the Redis sorted set with incremented view count and refreshes TTL.
     *
     * @param  string  $articleId  The article ID to increment views for
     * @param  int  $increment  Number of views to add (default: 1)
     */
    public function incrementView(string $articleId, int $increment = 1): void
    {
        Redis::zincrby(self::RANKING_KEY, $increment, $articleId);
        $this->updateExpiration();
    }

    /**
     * Get top ranked articles by views.
     *
     * Retrieves the highest viewed articles from Redis sorted set.
     *
     * @param  int  $limit  Maximum number of articles to return (default: 10)
     * @return Collection<int, array{article_id: string, score: float}> Collection of article IDs with scores
     */
    public function getTopArticles(int $limit = 10): Collection
    {
        /** @var array<string, float> $results */
        $results = Redis::zrevrange(self::RANKING_KEY, 0, $limit - 1, ['withscores' => true]);

        /** @var Collection<int, array{article_id: string, score: float}> $articles */
        $articles = collect();

        foreach ($results as $articleId => $score) {
            $articles->push([
                'article_id' => $articleId,
                'score' => (float) $score,
            ]);
        }

        return $articles;
    }

    /**
     * Get the ranking position of an article.
     *
     * Returns the 1-based rank of the article in the view count leaderboard.
     *
     * @param  string  $articleId  The article ID to get rank for
     * @return int|null The rank position (1-based) or null if article not in ranking
     */
    public function getArticleRank(string $articleId): ?int
    {
        $rank = Redis::zrevrank(self::RANKING_KEY, $articleId);

        if ($rank === false) {
            return null;
        }

        return (int) $rank + 1;
    }

    /**
     * Get the view score for an article.
     *
     * Returns the total view count for the article from Redis ranking.
     *
     * @param  string  $articleId  The article ID to get score for
     * @return float The view count score (0.0 if not found)
     */
    public function getArticleScore(string $articleId): float
    {
        $score = Redis::zscore(self::RANKING_KEY, $articleId);

        if ($score === false) {
            return 0.0;
        }

        return (float) $score;
    }

    /**
     * Remove an article from the ranking.
     *
     * Deletes the article entry from the Redis sorted set.
     *
     * @param  string  $articleId  The article ID to remove from ranking
     */
    public function removeArticle(string $articleId): void
    {
        Redis::zrem(self::RANKING_KEY, $articleId);
    }

    /**
     * Reset the entire ranking system.
     *
     * Deletes all entries from the Redis ranking sorted set.
     */
    public function resetRanking(): void
    {
        Redis::del(self::RANKING_KEY);
    }

    /**
     * Sync ranking data from database to Redis.
     *
     * Resets Redis ranking and rebuilds it from published article view counts in database.
     */
    public function syncFromDatabase(): void
    {
        $this->resetRanking();

        $articles = $this->articleRepository->getPublishedWithViews();

        foreach ($articles as $article) {
            $articleId = $article->id;
            assert(is_string($articleId));
            $viewCount = (float) $article->view_count;
            Redis::zadd(self::RANKING_KEY, $viewCount, $articleId);
        }

        $this->updateExpiration();
    }

    /**
     * Get ranking system statistics.
     *
     * Returns aggregate statistics including total articles, total views, and highest score.
     *
     * @return array{total_articles: int, total_views: float, top_score: float} Statistics array
     */
    public function getStatistics(): array
    {
        $totalArticles = Redis::zcard(self::RANKING_KEY);

        /** @var array<string, float> $topArticle */
        $topArticle = Redis::zrevrange(self::RANKING_KEY, 0, 0, ['withscores' => true]);
        $topScore = empty($topArticle) ? 0.0 : array_values($topArticle)[0];

        /** @var array<string, float> $allScores */
        $allScores = Redis::zrange(self::RANKING_KEY, 0, -1, ['withscores' => true]);
        $totalViews = 0.0;

        foreach ($allScores as $score) {
            $totalViews += (float) $score;
        }

        return [
            'total_articles' => (int) $totalArticles,
            'total_views' => $totalViews,
            'top_score' => $topScore,
        ];
    }

    /**
     * Update expiration time for ranking key.
     *
     * Refreshes the TTL on the Redis sorted set to prevent data expiration.
     */
    private function updateExpiration(): void
    {
        Redis::expire(self::RANKING_KEY, self::TTL_DAYS * 86400);
    }

    /**
     * Get enriched top articles with article details.
     *
     * Retrieves top ranked articles and enriches them with full article data from database.
     *
     * @param  int  $limit  Maximum number of articles to return (default: 10)
     * @return Collection<int, array{rank: int, article_id: string, views: int, article: array<string, mixed>|null}> Enriched ranking collection
     */
    public function getEnrichedTopArticles(int $limit = 10): Collection
    {
        $ranking = $this->getTopArticles($limit);

        /** @var array<int, string> $articleIds */
        $articleIds = $ranking->pluck('article_id')->toArray();
        $articles = $this->articleRepository->findByIds($articleIds)->keyBy('id');

        /** @var Collection<int, array{rank: int, article_id: string, views: int, article: array<string, mixed>|null}> $enrichedRanking */
        $enrichedRanking = $ranking->map(function (array $item, int|string $index) use ($articles): array {
            /** @var Article|null $article */
            $article = $articles->get($item['article_id']);

            return [
                'rank' => $index + 1,
                'article_id' => $item['article_id'],
                'views' => (int) $item['score'],
                'article' => $article ? [
                    'title' => $article->title,
                    'slug' => $article->slug,
                    'excerpt' => $article->excerpt,
                    'author_id' => $article->author_id,
                    'published_at' => $article->published_at?->toISOString(),
                ] : null,
            ];
        });

        return $enrichedRanking;
    }

    /**
     * Get enriched article ranking info.
     *
     * Retrieves ranking information for a specific article and enriches it with article data.
     *
     * @param  string  $articleId  The article ID to get ranking info for
     * @return array{article_id: string, rank: int|null, views: int, article: array<string, mixed>} Enriched ranking data
     */
    public function getEnrichedArticleRanking(string $articleId): array
    {
        $article = $this->articleRepository->findById($articleId);

        if (! $article instanceof \App\Models\Article) {
            return [
                'article_id' => $articleId,
                'rank' => null,
                'views' => 0,
                'article' => [],
            ];
        }

        $rank = $this->getArticleRank($articleId);
        $score = $this->getArticleScore($articleId);

        return [
            'article_id' => $articleId,
            'rank' => $rank,
            'views' => (int) $score,
            'article' => [
                'title' => $article->title,
                'slug' => $article->slug,
                'view_count' => $article->view_count,
            ],
        ];
    }
}
