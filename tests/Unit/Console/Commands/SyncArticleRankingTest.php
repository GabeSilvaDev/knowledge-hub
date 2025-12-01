<?php

use App\Contracts\ArticleRankingServiceInterface;
use Illuminate\Console\Command;

use function Pest\Laravel\artisan;
use function Pest\Laravel\mock;

describe('SyncArticleRanking Command', function (): void {
    it('executes successfully and displays sync message', function (): void {
        $rankingService = mock(ArticleRankingServiceInterface::class);

        $rankingService->shouldReceive('syncFromDatabase')
            ->once()
            ->andReturn();

        $rankingService->shouldReceive('getStatistics')
            ->once()
            ->andReturn([
                'total_articles' => 10,
                'total_views' => 1500.0,
                'top_score' => 500.0,
            ]);

        artisan('articles:sync-ranking')
            ->assertExitCode(Command::SUCCESS)
            ->expectsOutput('Synchronizing article ranking...')
            ->expectsOutput('✓ Ranking synchronized successfully!');
    });

    it('displays statistics table after sync', function (): void {
        $rankingService = mock(ArticleRankingServiceInterface::class);

        $rankingService->shouldReceive('syncFromDatabase')
            ->once()
            ->andReturn();

        $rankingService->shouldReceive('getStatistics')
            ->once()
            ->andReturn([
                'total_articles' => 5,
                'total_views' => 250.0,
                'top_score' => 100.0,
            ]);

        artisan('articles:sync-ranking')
            ->assertExitCode(Command::SUCCESS)
            ->expectsTable(['Metric', 'Value'], [
                ['Total articles', 5],
                ['Total views', '250'],
                ['Highest score', '100'],
            ]);
    });

    it('handles empty statistics correctly', function (): void {
        $rankingService = mock(ArticleRankingServiceInterface::class);

        $rankingService->shouldReceive('syncFromDatabase')
            ->once()
            ->andReturn();

        $rankingService->shouldReceive('getStatistics')
            ->once()
            ->andReturn([
                'total_articles' => 0,
                'total_views' => 0.0,
                'top_score' => 0.0,
            ]);

        artisan('articles:sync-ranking')
            ->assertExitCode(Command::SUCCESS);
    });
});
