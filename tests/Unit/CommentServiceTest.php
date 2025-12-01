<?php

use App\Models\Article;
use App\Models\Comment;
use App\Models\User;
use App\Repositories\CommentRepository;
use App\Services\CommentService;

beforeEach(function (): void {
    Comment::query()->delete();
    Article::query()->delete();
    User::query()->delete();

    $this->commentRepository = new CommentRepository;
    $this->commentService = new CommentService($this->commentRepository);
});

describe('CommentService Unit Tests', function (): void {
    it('finds comment by id', function (): void {
        $user = User::factory()->create();
        $article = Article::factory()->create(['author_id' => $user->id]);
        $comment = Comment::factory()->create([
            'article_id' => $article->id,
            'user_id' => $user->id,
        ]);

        $foundComment = $this->commentService->findCommentById($comment->id);

        expect($foundComment)->not->toBeNull()
            ->and($foundComment->id)->toBe($comment->id);
    });

    it('returns null when comment not found', function (): void {
        $foundComment = $this->commentService->findCommentById('non-existent-id');

        expect($foundComment)->toBeNull();
    });
});
