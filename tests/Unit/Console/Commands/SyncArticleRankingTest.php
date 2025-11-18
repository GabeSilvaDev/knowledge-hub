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
            ->expectsOutput('Sincronizando ranking de artigos...')
            ->expectsOutput('✓ Ranking sincronizado com sucesso!');
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
            ->expectsTable(['Métrica', 'Valor'], [
                ['Total de artigos', 5],
                ['Total de visualizações', '250'],
                ['Maior pontuação', '100'],
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
