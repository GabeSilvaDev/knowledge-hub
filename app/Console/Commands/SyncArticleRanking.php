<?php

namespace App\Console\Commands;

use App\Contracts\ArticleRankingServiceInterface;
use Illuminate\Console\Command;

class SyncArticleRanking extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'articles:sync-ranking';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync article ranking from MongoDB to Redis';

    /**
     * Execute the console command.
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
