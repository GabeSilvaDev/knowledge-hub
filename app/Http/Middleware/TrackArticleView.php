<?php

namespace App\Http\Middleware;

use App\Contracts\ArticleRankingServiceInterface;
use App\Models\Article;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackArticleView
{
    public function __construct(
        private readonly ArticleRankingServiceInterface $rankingService
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $request->isMethod('GET')) {
            return $response;
        }

        /** @var Article|null $article */
        $article = $request->route('article');

        if ($article instanceof Article) {
            $articleId = $article->id;
            assert(is_string($articleId));

            $this->rankingService->incrementView($articleId);

            $article->withoutVersioning(function () use ($article): void {
                $article->increment('view_count');
            });
        }

        return $response;
    }
}
