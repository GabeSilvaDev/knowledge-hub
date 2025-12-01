<?php

use App\Contracts\Neo4jRepositoryInterface;
use App\Models\Article;
use App\Models\Like;
use App\Models\User;
use App\Observers\LikeNeo4jObserver;

beforeEach(function (): void {
    Like::query()->delete();
    Article::query()->delete();
    User::query()->delete();

    $this->mockNeo4j = Mockery::mock(Neo4jRepositoryInterface::class);
    $this->observer = new LikeNeo4jObserver($this->mockNeo4j);
    $this->user = User::factory()->create();
    $this->article = Article::factory()->create([
        'author_id' => $this->user->id,
    ]);
});

afterEach(function (): void {
    Mockery::close();
});

describe('LikeNeo4jObserver', function (): void {
    describe('created', function (): void {
        it('syncs like relationship to Neo4j', function (): void {
            $like = Like::create([
                'user_id' => $this->user->id,
                'article_id' => $this->article->id,
            ]);

            $this->mockNeo4j->shouldReceive('syncLike')
                ->once()
                ->with((string) $this->user->id, (string) $this->article->id);

            $this->observer->created($like);
        });
    });

    describe('deleted', function (): void {
        it('deletes like relationship from Neo4j', function (): void {
            $like = Like::create([
                'user_id' => $this->user->id,
                'article_id' => $this->article->id,
            ]);

            $this->mockNeo4j->shouldReceive('deleteLike')
                ->once()
                ->with((string) $this->user->id, (string) $this->article->id);

            $this->observer->deleted($like);
        });
    });
});
