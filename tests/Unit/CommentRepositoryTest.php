<?php

use App\Models\Article;
use App\Models\Comment;
use App\Models\User;
use App\Repositories\CommentRepository;

beforeEach(function (): void {
    Comment::query()->delete();
    Article::query()->delete();
    User::query()->delete();

    $this->repository = new CommentRepository;
    $this->user = User::factory()->create();
    $this->article = Article::factory()->create(['author_id' => $this->user->id]);
});

describe('CommentRepository Unit Tests', function (): void {
    it('finds comment by id', function (): void {
        $comment = Comment::factory()->create([
            'article_id' => $this->article->id,
            'user_id' => $this->user->id,
        ]);

        $foundComment = $this->repository->findById($comment->id);

        expect($foundComment)->not->toBeNull()
            ->and($foundComment->id)->toBe($comment->id);
    });

    it('returns null when comment not found by id', function (): void {
        $foundComment = $this->repository->findById('non-existent-id');

        expect($foundComment)->toBeNull();
    });

    it('creates query builder with filters', function (): void {
        $queryBuilder = $this->repository->query();

        expect($queryBuilder)->toBeInstanceOf(\Spatie\QueryBuilder\QueryBuilder::class);
    });
});
