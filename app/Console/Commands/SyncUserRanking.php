<?php

namespace App\Console\Commands;

use App\Contracts\UserRankingServiceInterface;
use Illuminate\Console\Command;

/**
 * Sync User Ranking Command.
 *
 * Synchronizes user influence ranking from MongoDB to Redis.
 */
class SyncUserRanking extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:sync-ranking';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync user influence ranking from MongoDB to Redis';

    /**
     * Execute the console command.
     *
     * @param  UserRankingServiceInterface  $rankingService  Service for managing user rankings
     * @return int Command exit code
     */
    public function handle(UserRankingServiceInterface $rankingService): int
    {
        $this->info('Sincronizando ranking de usuários...');

        $rankingService->syncFromDatabase();

        $stats = $rankingService->getStatistics();

        $this->info('Ranking sincronizado com sucesso!');
        $this->table(
            ['Métrica', 'Valor'],
            [
                ['Total de Usuários', $stats['total_users']],
                ['Score Total', number_format($stats['total_score'], 2)],
                ['Maior Score', number_format($stats['top_score'], 2)],
                ['Média de Score', number_format($stats['average_score'], 2)],
            ]
        );

        return Command::SUCCESS;
    }
}
