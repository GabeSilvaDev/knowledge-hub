<?php

use App\Contracts\RecommendationServiceInterface;

use function Pest\Laravel\artisan;

describe('SyncNeo4jCommand', function (): void {
    describe('neo4j:sync command', function (): void {
        it('returns failure when Neo4j is not available', function (): void {
            $mockService = Mockery::mock(RecommendationServiceInterface::class);
            $mockService->shouldReceive('isAvailable')
                ->once()
                ->andReturn(false);

            $this->app->instance(RecommendationServiceInterface::class, $mockService);

            artisan('neo4j:sync')
                ->expectsOutput('Iniciando sincronização com Neo4j...')
                ->expectsOutput('Neo4j não está disponível. Verifique a conexão.')
                ->assertExitCode(1);
        });

        it('syncs all data successfully when Neo4j is available', function (): void {
            $mockService = Mockery::mock(RecommendationServiceInterface::class);
            $mockService->shouldReceive('isAvailable')
                ->once()
                ->andReturn(true);
            $mockService->shouldReceive('syncFromDatabase')
                ->once()
                ->andReturn([
                    'users' => 10,
                    'articles' => 25,
                    'follows' => 50,
                    'likes' => 100,
                ]);
            $mockService->shouldReceive('getStatistics')
                ->once()
                ->andReturn([
                    'users' => 10,
                    'articles' => 25,
                    'follows' => 50,
                    'likes' => 100,
                    'tags' => 15,
                    'categories' => 5,
                ]);

            $this->app->instance(RecommendationServiceInterface::class, $mockService);

            artisan('neo4j:sync')
                ->expectsOutput('Iniciando sincronização com Neo4j...')
                ->expectsOutput('✓ Neo4j conectado com sucesso!')
                ->expectsOutput('Sincronizando todos os dados...')
                ->expectsOutput('✓ Sincronização concluída com sucesso!')
                ->assertExitCode(0);
        });

        it('shows entity-specific message when --entity option is provided', function (): void {
            $mockService = Mockery::mock(RecommendationServiceInterface::class);
            $mockService->shouldReceive('isAvailable')
                ->once()
                ->andReturn(true);
            $mockService->shouldReceive('syncFromDatabase')
                ->once()
                ->andReturn([
                    'users' => 5,
                    'articles' => 0,
                    'follows' => 0,
                    'likes' => 0,
                ]);
            $mockService->shouldReceive('getStatistics')
                ->once()
                ->andReturn([
                    'users' => 5,
                    'articles' => 0,
                    'follows' => 0,
                    'likes' => 0,
                    'tags' => 0,
                    'categories' => 0,
                ]);

            $this->app->instance(RecommendationServiceInterface::class, $mockService);

            artisan('neo4j:sync', ['--entity' => 'users'])
                ->expectsOutput('Sincronizando apenas: users')
                ->assertExitCode(0);
        });

        it('displays sync statistics table correctly', function (): void {
            $mockService = Mockery::mock(RecommendationServiceInterface::class);
            $mockService->shouldReceive('isAvailable')
                ->once()
                ->andReturn(true);
            $mockService->shouldReceive('syncFromDatabase')
                ->once()
                ->andReturn([
                    'users' => 1000,
                    'articles' => 500,
                    'follows' => 2000,
                    'likes' => 5000,
                ]);
            $mockService->shouldReceive('getStatistics')
                ->once()
                ->andReturn([
                    'users' => 1000,
                    'articles' => 500,
                    'follows' => 2000,
                    'likes' => 5000,
                    'tags' => 100,
                    'categories' => 20,
                ]);

            $this->app->instance(RecommendationServiceInterface::class, $mockService);

            artisan('neo4j:sync')
                ->expectsOutput('Estatísticas do grafo Neo4j:')
                ->assertExitCode(0);
        });

        it('handles empty entity option as full sync', function (): void {
            $mockService = Mockery::mock(RecommendationServiceInterface::class);
            $mockService->shouldReceive('isAvailable')
                ->once()
                ->andReturn(true);
            $mockService->shouldReceive('syncFromDatabase')
                ->once()
                ->andReturn([
                    'users' => 0,
                    'articles' => 0,
                    'follows' => 0,
                    'likes' => 0,
                ]);
            $mockService->shouldReceive('getStatistics')
                ->once()
                ->andReturn([
                    'users' => 0,
                    'articles' => 0,
                    'follows' => 0,
                    'likes' => 0,
                    'tags' => 0,
                    'categories' => 0,
                ]);

            $this->app->instance(RecommendationServiceInterface::class, $mockService);

            artisan('neo4j:sync', ['--entity' => ''])
                ->expectsOutput('Sincronizando todos os dados...')
                ->assertExitCode(0);
        });
    });
});
