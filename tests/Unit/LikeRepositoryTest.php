<?php

use App\Models\Article;
use App\Models\Like;
use App\Models\User;
use App\Repositories\LikeRepository;

beforeEach(function (): void {
    Like::query()->delete();
    Article::query()->delete();
    User::query()->delete();

    $this->repository = new LikeRepository;
    $this->article = Article::factory()->create();
    $this->user = User::factory()->create();
});

describe('LikeRepository Unit Tests', function (): void {
    it('counts likes for an article', function (): void {
        Like::factory()->count(5)->create(['article_id' => $this->article->id]);

        $count = $this->repository->getCountByArticle($this->article->id);

        expect($count)->toBe(5);
    });
});
