<?php

namespace App\Console\Commands;

use App\Contracts\ArticleRankingServiceInterface;
use Illuminate\Console\Command;

class SyncArticleRanking extends Command
{
    /**
     * The name and signature of the console command.
     *
     * Command to synchronize article ranking data from MongoDB to Redis.
     *
     * @var string
     */
    protected $signature = 'articles:sync-ranking';

    /**
     * The console command description.
     *
     * Detailed description of what the command does for artisan list.
     *
     * @var string
     */
    protected $description = 'Sync article ranking from MongoDB to Redis';

    /**
     * Execute the console command.
     *
     * Synchronizes article view counts from the database to the Redis ranking system
     * and displays statistics about the synchronization.
     *
     * @param  ArticleRankingServiceInterface  $rankingService  Service for managing article rankings
     * @return int Command exit code (SUCCESS or FAILURE)
     */
    public function handle(ArticleRankingServiceInterface $rankingService): int
    {
        $this->info('Sincronizando ranking de artigos...');

        $rankingService->syncFromDatabase();

        $stats = $rankingService->getStatistics();

        $this->info('✓ Ranking sincronizado com sucesso!');
        $this->table(
            ['Métrica', 'Valor'],
            [
                ['Total de artigos', $stats['total_articles']],
                ['Total de visualizações', number_format($stats['total_views'], 0, ',', '.')],
                ['Maior pontuação', number_format($stats['top_score'], 0, ',', '.')],
            ]
        );

        return Command::SUCCESS;
    }
}
