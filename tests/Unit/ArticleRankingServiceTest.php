<?php

use App\Services\ArticleRankingService;

describe('ArticleRankingService Unit Tests', function (): void {
    it('returns empty ranking for non-existent article', function (): void {
        $service = app(ArticleRankingService::class);

        $ranking = $service->getEnrichedArticleRanking('non-existent-id');

        expect($ranking)->toMatchArray([
            'article_id' => 'non-existent-id',
            'rank' => null,
            'views' => 0,
            'article' => [],
        ]);
    });
});
