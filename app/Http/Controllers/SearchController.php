<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Contracts\SearchServiceInterface;
use App\Http\Requests\AutocompleteRequest;
use App\Http\Requests\SearchRequest;
use Illuminate\Http\JsonResponse;

/**
 * Search Controller.
 *
 * Handles HTTP requests for article search operations.
 */
class SearchController extends Controller
{
    public function __construct(
        private readonly SearchServiceInterface $searchService
    ) {}

    /**
     * Search articles by query term with optional filters.
     *
     * @param  SearchRequest  $request  The validated search request
     * @return JsonResponse The paginated search results
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
     * Get autocomplete suggestions for a search query.
     *
     * @param  AutocompleteRequest  $request  The validated autocomplete request
     * @return JsonResponse The autocomplete suggestions
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
     * Synchronize all articles with the search index.
     *
     * Only administrators can execute this action.
     *
     * @return JsonResponse The synchronization result with count
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
