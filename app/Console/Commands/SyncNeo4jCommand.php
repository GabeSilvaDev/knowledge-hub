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
        $this->info('Iniciando sincronização com Neo4j...');

        if (! $recommendationService->isAvailable()) {
            $this->error('Neo4j não está disponível. Verifique a conexão.');

            return Command::FAILURE;
        }

        $this->info('✓ Neo4j conectado com sucesso!');

        /** @var string|null $entity */
        $entity = $this->option('entity');

        if ($entity !== null && $entity !== '') {
            $this->info("Sincronizando apenas: {$entity}");
        } else {
            $this->info('Sincronizando todos os dados...');
        }

        $this->newLine();
        $this->output->write('  <comment>→ Sincronizando...</comment>');

        $stats = $recommendationService->syncFromDatabase();

        $this->output->writeln(' <info>✓</info>');
        $this->newLine();

        $this->info('✓ Sincronização concluída com sucesso!');
        $this->newLine();

        $this->table(
            ['Entidade', 'Sincronizados'],
            [
                ['Usuários', number_format($stats['users'], 0, ',', '.')],
                ['Artigos', number_format($stats['articles'], 0, ',', '.')],
                ['Seguidores', number_format($stats['follows'], 0, ',', '.')],
                ['Curtidas', number_format($stats['likes'], 0, ',', '.')],
            ]
        );

        $this->newLine();

        $graphStats = $recommendationService->getStatistics();
        $this->info('Estatísticas do grafo Neo4j:');
        $this->table(
            ['Tipo de Nó/Relacionamento', 'Quantidade'],
            [
                ['Usuários (User)', number_format($graphStats['users'], 0, ',', '.')],
                ['Artigos (Article)', number_format($graphStats['articles'], 0, ',', '.')],
                ['Relacionamentos FOLLOWS', number_format($graphStats['follows'], 0, ',', '.')],
                ['Relacionamentos LIKES', number_format($graphStats['likes'], 0, ',', '.')],
                ['Tags', number_format($graphStats['tags'], 0, ',', '.')],
                ['Categorias', number_format($graphStats['categories'], 0, ',', '.')],
            ]
        );

        return Command::SUCCESS;
    }
}
