<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Contracts\SearchServiceInterface;
use App\Http\Requests\AutocompleteRequest;
use App\Http\Requests\SearchRequest;
use Illuminate\Http\JsonResponse;

/**
 * SearchController
 *
 * Gerencia as requisições de busca de artigos.
 */
class SearchController extends Controller
{
    public function __construct(
        private readonly SearchServiceInterface $searchService
    ) {}

    /**
     * Busca artigos por termo de pesquisa com filtros opcionais.
     */
    public function search(SearchRequest $request): JsonResponse
    {
        $results = $this->searchService->search(
            query: $request->getQuery(),
            filters: $request->getFilters(),
            perPage: $request->getPerPage()
        );

        return response()->json([
            'data' => $results->items(),
            'meta' => [
                'total' => $results->total(),
                'per_page' => $results->perPage(),
                'current_page' => $results->currentPage(),
                'last_page' => $results->lastPage(),
                'from' => $results->firstItem(),
                'to' => $results->lastItem(),
            ],
            'links' => [
                'first' => $results->url(1),
                'last' => $results->url($results->lastPage()),
                'prev' => $results->previousPageUrl(),
                'next' => $results->nextPageUrl(),
            ],
        ]);
    }

    /**
     * Retorna sugestões de autocomplete.
     */
    public function autocomplete(AutocompleteRequest $request): JsonResponse
    {
        $suggestions = $this->searchService->autocomplete(
            query: $request->getQuery(),
            limit: $request->getLimit()
        );

        return response()->json([
            'data' => $suggestions,
        ]);
    }

    /**
     * Sincroniza todos os artigos com o índice de busca.
     * Apenas administradores podem executar.
     */
    public function sync(): JsonResponse
    {
        $count = $this->searchService->syncAll();

        return response()->json([
            'message' => 'Articles synchronized successfully.',
            'count' => $count,
        ]);
    }
}
