<?php

namespace App\Observers;

use App\Models\Article;
use App\Models\Like;

/**
 * Like Observer.
 *
 * Observes like events to update article like counts automatically.
 */
final readonly class LikeObserver
{
    /**
     * Handle the Like "created" event.
     *
     * @param  Like  $like  The created like
     */
    public function created(Like $like): void
    {
        $this->updateArticleLikeCount($like->article_id);
    }

    /**
     * Handle the Like "deleted" event.
     *
     * @param  Like  $like  The deleted like
     */
    public function deleted(Like $like): void
    {
        $this->updateArticleLikeCount($like->article_id);
    }

    /**
     * Update the like count for an article.
     *
     * @param  string  $articleId  The article ID to update count for
     */
    private function updateArticleLikeCount(string $articleId): void
    {
        /** @var Article $article */
        $article = Article::find($articleId);

        $count = Like::where('article_id', $articleId)->count();

        $article->withoutVersioning(function () use ($article, $count): void {
            $article->update(['like_count' => $count]);
        });
    }
}
