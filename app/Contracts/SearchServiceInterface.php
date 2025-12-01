<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\Article;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Interface SearchServiceInterface
 *
 * Define o contrato para serviços de busca de artigos.
 */
interface SearchServiceInterface
{
    /**
     * Busca artigos por termo de pesquisa.
     *
     * @param  string  $query  Termo de busca
     * @param  array<string, mixed>  $filters  Filtros adicionais (autor, tags, status, datas)
     * @param  int  $perPage  Número de resultados por página
     * @return LengthAwarePaginator<int, Article>
     */
    public function search(string $query, array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Retorna sugestões de autocomplete baseadas no termo.
     *
     * @param  string  $query  Termo parcial de busca
     * @param  int  $limit  Limite de sugestões
     * @return array<int, array{id: string, title: string, slug: string, excerpt: string|null}>
     */
    public function autocomplete(string $query, int $limit = 10): array;

    /**
     * Mapeia uma coleção de artigos para o formato de autocomplete.
     *
     * @param  \Illuminate\Support\Collection<int, \App\Models\Article>  $articles
     * @return array<int, array{id: string, title: string, slug: string, excerpt: string|null}>
     */
    public function mapArticlesToAutocomplete($articles): array;

    /**
     * Sincroniza todos os artigos com o índice de busca.
     *
     * @return int Número de artigos indexados
     */
    public function syncAll(): int;

    /**
     * Remove um artigo do índice de busca.
     *
     * @param  string  $articleId  ID do artigo
     */
    public function removeFromIndex(string $articleId): bool;
}
