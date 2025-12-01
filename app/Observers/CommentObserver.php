<?php

namespace App\Observers;

use App\Models\Article;
use App\Models\Comment;

/**
 * Comment Observer.
 *
 * Observes comment events to update article comment counts automatically.
 */
final readonly class CommentObserver
{
    /**
     * Handle the Comment "created" event.
     *
     * @param  Comment  $comment  The created comment
     */
    public function created(Comment $comment): void
    {
        $this->updateArticleCommentCount($comment->article_id);
    }

    /**
     * Handle the Comment "deleted" event.
     *
     * @param  Comment  $comment  The deleted comment
     */
    public function deleted(Comment $comment): void
    {
        $this->updateArticleCommentCount($comment->article_id);
    }

    /**
     * Handle the Comment "restored" event.
     *
     * @param  Comment  $comment  The restored comment
     */
    public function restored(Comment $comment): void
    {
        $this->updateArticleCommentCount($comment->article_id);
    }

    /**
     * Handle the Comment "force deleted" event.
     *
     * @param  Comment  $comment  The force deleted comment
     */
    public function forceDeleted(Comment $comment): void
    {
        $this->updateArticleCommentCount($comment->article_id);
    }

    /**
     * Update the comment count for an article.
     *
     * @param  string  $articleId  The article ID to update count for
     */
    private function updateArticleCommentCount(string $articleId): void
    {
        /** @var Article $article */
        $article = Article::find($articleId);

        $count = Comment::where('article_id', $articleId)->count();

        $article->withoutVersioning(function () use ($article, $count): void {
            $article->update(['comment_count' => $count]);
        });
    }
}
