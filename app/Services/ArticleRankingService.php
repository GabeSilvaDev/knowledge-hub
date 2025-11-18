<?php

namespace App\Services;

use App\Contracts\ArticleRankingServiceInterface;
use App\Models\Article;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Redis;

class ArticleRankingService implements ArticleRankingServiceInterface
{
    private const string RANKING_KEY = 'articles:ranking:views';

    private const int TTL_DAYS = 90;

    public function incrementView(string $articleId, int $increment = 1): void
    {
        Redis::zincrby(self::RANKING_KEY, $increment, $articleId);
        $this->updateExpiration();
    }

    /**
     * @return Collection<int, array{article_id: string, score: float}>
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

    public function getArticleRank(string $articleId): ?int
    {
        $rank = Redis::zrevrank(self::RANKING_KEY, $articleId);

        if ($rank === false) {
            return null;
        }

        return (int) $rank + 1;
    }

    public function getArticleScore(string $articleId): float
    {
        $score = Redis::zscore(self::RANKING_KEY, $articleId);

        if ($score === false) {
            return 0.0;
        }

        return (float) $score;
    }

    public function removeArticle(string $articleId): void
    {
        Redis::zrem(self::RANKING_KEY, $articleId);
    }

    public function resetRanking(): void
    {
        Redis::del(self::RANKING_KEY);
    }

    public function syncFromDatabase(): void
    {
        $this->resetRanking();

        Article::query()
            ->where('status', 'published')
            ->where('view_count', '>', 0)
            ->chunk(100, function ($articles): void {
                /** @var Collection<int, Article> $articles */
                foreach ($articles as $article) {
                    $articleId = $article->id;
                    assert(is_string($articleId));
                    $viewCount = (float) $article->view_count;
                    Redis::zadd(self::RANKING_KEY, $viewCount, $articleId);
                }
            });

        $this->updateExpiration();
    }

    /**
     * @return array{total_articles: int, total_views: float, top_score: float}
     */
    public function getStatistics(): array
    {
        $totalArticles = Redis::zcard(self::RANKING_KEY);

        /** @var array<string, float> $topArticle */
        $topArticle = Redis::zrevrange(self::RANKING_KEY, 0, 0, ['withscores' => true]);
        $topScore = empty($topArticle) ? 0.0 : (float) array_values($topArticle)[0];

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
     */
    private function updateExpiration(): void
    {
        Redis::expire(self::RANKING_KEY, self::TTL_DAYS * 86400);
    }
}
