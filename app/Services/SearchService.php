<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\SearchServiceInterface;
use App\Enums\ArticleStatus;
use App\Models\Article;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * SearchService
 *
 * Implementa a lógica de busca de artigos usando Laravel Scout e Meilisearch.
 */
class SearchService implements SearchServiceInterface
{
    /**
     * Busca artigos por termo de pesquisa.
     *
     * @param  string  $query  Termo de busca
     * @param  array<string, mixed>  $filters  Filtros adicionais (author, tags, status, date_from, date_to)
     * @param  int  $perPage  Número de resultados por página
     * @return LengthAwarePaginator<int, Article>
     */
    public function search(string $query, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $searchQuery = Article::search($query);

        $searchQuery->query(fn ($builder) => $this->applyFilters($builder, $filters));

        return $searchQuery->paginate($perPage);
    }

    /**
     * Aplica filtros na query de busca.
     *
     * @param  mixed  $builder
     * @param  array<string, mixed>  $filters
     * @return mixed
     */
    private function applyFilters($builder, array $filters)
    {
        if (isset($filters['author_id']) && $filters['author_id'] !== '') {
            /* @phpstan-ignore method.nonObject */
            $builder->where('author_id', $filters['author_id']);
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            /* @phpstan-ignore method.nonObject */
            $builder->where('status', $filters['status']);
        } else {
            /* @phpstan-ignore method.nonObject */
            $builder->where('status', ArticleStatus::PUBLISHED->value);
        }

        if (isset($filters['type']) && $filters['type'] !== '') {
            /* @phpstan-ignore method.nonObject */
            $builder->where('type', $filters['type']);
        }

        if (isset($filters['tags']) && is_array($filters['tags']) && count($filters['tags']) > 0) {
            /* @phpstan-ignore method.nonObject */
            $builder->whereIn('tags', $filters['tags']);
        }

        if (isset($filters['categories']) && is_array($filters['categories']) && count($filters['categories']) > 0) {
            /* @phpstan-ignore method.nonObject */
            $builder->whereIn('categories', $filters['categories']);
        }

        if (isset($filters['date_from']) && $filters['date_from'] !== '') {
            /* @phpstan-ignore method.nonObject */
            $builder->where('published_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to']) && $filters['date_to'] !== '') {
            /* @phpstan-ignore method.nonObject */
            $builder->where('published_at', '<=', $filters['date_to']);
        }

        return $builder;
    }

    /**
     * Retorna sugestões de autocomplete baseadas no termo.
     *
     * @param  string  $query  Termo parcial de busca
     * @param  int  $limit  Limite de sugestões
     * @return array<int, array{id: string, title: string, slug: string, excerpt: string|null}>
     */
    public function autocomplete(string $query, int $limit = 10): array
    {
        if (strlen($query) < 2) {
            return [];
        }

        $results = Article::search($query)
            /* @phpstan-ignore method.nonObject */
            ->query(fn ($builder) => $builder->where('status', ArticleStatus::PUBLISHED->value))
            ->take($limit)
            ->get();

        return $this->mapArticlesToAutocomplete($results);
    }

    /**
     * Mapeia uma coleção de artigos para o formato de autocomplete.
     *
     * @param  \Illuminate\Support\Collection<int, Article>  $articles
     * @return array<int, array{id: string, title: string, slug: string, excerpt: string|null}>
     */
    public function mapArticlesToAutocomplete($articles): array
    {
        /** @var array<int, array{id: string, title: string, slug: string, excerpt: string|null}> $mapped */
        $mapped = $articles->map(function (Article $article): array {
            $id = $article->id;
            $idString = is_string($id) ? $id : '';

            return [
                'id' => $idString,
                'title' => $article->title,
                'slug' => $article->slug,
                'excerpt' => $article->excerpt ? substr((string) $article->excerpt, 0, 100) : null,
            ];
        })->toArray();

        return $mapped;
    }

    /**
     * Sincroniza todos os artigos com o índice de busca.
     *
     * @return int Número de artigos indexados
     */
    public function syncAll(): int
    {
        $count = Article::count();

        Article::makeAllSearchable();

        return $count;
    }

    /**
     * Remove um artigo do índice de busca.
     *
     * @param  string  $articleId  ID do artigo
     */
    public function removeFromIndex(string $articleId): bool
    {
        $article = Article::find($articleId);

        if (! $article) {
            return false;
        }

        $article->unsearchable();

        return true;
    }
}
