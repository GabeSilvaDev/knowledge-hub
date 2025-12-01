<?php

namespace App\Console\Commands;

use App\Contracts\RecommendationServiceInterface;
use Illuminate\Console\Command;

/**
 * Sync Neo4j Command.
 *
 * Synchronizes data from MongoDB to Neo4j graph database.
 */
class SyncNeo4jCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'neo4j:sync
                            {--entity= : Sync specific entity (users, articles, follows, likes)}
                            {--clear : Clear all Neo4j data before syncing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync data from MongoDB to Neo4j graph database for recommendations';

    /**
     * Execute the console command.
     */
    public function handle(RecommendationServiceInterface $recommendationService): int
    {
        $this->info('Starting Neo4j synchronization...');

        if (! $recommendationService->isAvailable()) {
            $this->error('Neo4j is not available. Please check the connection.');

            return Command::FAILURE;
        }

        $this->info('✓ Neo4j connected successfully!');

        /** @var string|null $entity */
        $entity = $this->option('entity');

        if ($entity !== null && $entity !== '') {
            $this->info("Synchronizing only: {$entity}");
        } else {
            $this->info('Synchronizing all data...');
        }

        $this->newLine();
        $this->output->write('  <comment>→ Synchronizing...</comment>');

        $stats = $recommendationService->syncFromDatabase();

        $this->output->writeln(' <info>✓</info>');
        $this->newLine();

        $this->info('✓ Synchronization completed successfully!');
        $this->newLine();

        $this->table(
            ['Entity', 'Synchronized'],
            [
                ['Users', number_format($stats['users'], 0, '.', ',')],
                ['Articles', number_format($stats['articles'], 0, '.', ',')],
                ['Followers', number_format($stats['follows'], 0, '.', ',')],
                ['Likes', number_format($stats['likes'], 0, '.', ',')],
            ]
        );

        $this->newLine();

        $graphStats = $recommendationService->getStatistics();
        $this->info('Neo4j graph statistics:');
        $this->table(
            ['Node/Relationship Type', 'Count'],
            [
                ['Users (User)', number_format($graphStats['users'], 0, '.', ',')],
                ['Articles (Article)', number_format($graphStats['articles'], 0, '.', ',')],
                ['FOLLOWS Relationships', number_format($graphStats['follows'], 0, '.', ',')],
                ['LIKES Relationships', number_format($graphStats['likes'], 0, '.', ',')],
                ['Tags', number_format($graphStats['tags'], 0, '.', ',')],
                ['Categories', number_format($graphStats['categories'], 0, '.', ',')],
            ]
        );

        return Command::SUCCESS;
    }
}
