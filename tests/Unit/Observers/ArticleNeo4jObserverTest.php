<?php

use App\Contracts\Neo4jRepositoryInterface;
use App\Models\Article;
use App\Models\User;
use App\Observers\ArticleNeo4jObserver;

beforeEach(function (): void {
    Article::query()->delete();
    User::query()->delete();

    $this->mockNeo4j = Mockery::mock(Neo4jRepositoryInterface::class);
    $this->observer = new ArticleNeo4jObserver($this->mockNeo4j);
    $this->user = User::factory()->create();
});

afterEach(function (): void {
    Mockery::close();
});

describe('ArticleNeo4jObserver', function (): void {
    describe('created', function (): void {
        it('syncs published article to Neo4j', function (): void {
            $article = Article::factory()->make([
                'author_id' => $this->user->id,
                'status' => 'published',
                'tags' => ['laravel', 'php'],
                'categories' => ['programming'],
            ]);
            $article->save();

            $this->mockNeo4j->shouldReceive('syncArticle')
                ->once()
                ->with(Mockery::on(
                    fn ($data): bool => $data['id'] === $article->id
                    && $data['status'] === 'published'
                ));

            $this->observer->created($article);
        });

        it('does not sync draft article to Neo4j', function (): void {
            $article = Article::factory()->make([
                'author_id' => $this->user->id,
                'status' => 'draft',
            ]);
            $article->save();

            $this->mockNeo4j->shouldNotReceive('syncArticle');

            $this->observer->created($article);
        });

        it('does not sync private article to Neo4j', function (): void {
            $article = Article::factory()->make([
                'author_id' => $this->user->id,
                'status' => 'private',
            ]);
            $article->save();

            $this->mockNeo4j->shouldNotReceive('syncArticle');

            $this->observer->created($article);
        });

        it('does not sync archived article to Neo4j', function (): void {
            $article = Article::factory()->make([
                'author_id' => $this->user->id,
                'status' => 'archived',
            ]);
            $article->save();

            $this->mockNeo4j->shouldNotReceive('syncArticle');

            $this->observer->created($article);
        });
    });

    describe('updated', function (): void {
        it('syncs published article on update', function (): void {
            $article = Article::factory()->make([
                'author_id' => $this->user->id,
                'status' => 'published',
            ]);
            $article->save();

            $this->mockNeo4j->shouldReceive('syncArticle')
                ->once()
                ->with(Mockery::on(fn ($data): bool => $data['id'] === $article->id));

            $this->observer->updated($article);
        });

        it('does not sync non-published article on update', function (): void {
            $article = Article::factory()->make([
                'author_id' => $this->user->id,
                'status' => 'draft',
            ]);
            $article->save();

            $this->mockNeo4j->shouldNotReceive('syncArticle');

            $this->observer->updated($article);
        });
    });

    describe('deleted', function (): void {
        it('deletes article from Neo4j', function (): void {
            $article = Article::factory()->make([
                'author_id' => $this->user->id,
            ]);
            $article->save();

            $this->mockNeo4j->shouldReceive('deleteArticle')
                ->once()
                ->with($article->id);

            $this->observer->deleted($article);
        });
    });

    describe('restored', function (): void {
        it('syncs restored published article to Neo4j', function (): void {
            $article = Article::factory()->make([
                'author_id' => $this->user->id,
                'status' => 'published',
            ]);
            $article->save();

            $this->mockNeo4j->shouldReceive('syncArticle')
                ->once()
                ->with(Mockery::on(fn ($data): bool => $data['id'] === $article->id));

            $this->observer->restored($article);
        });

        it('does not sync restored draft article', function (): void {
            $article = Article::factory()->make([
                'author_id' => $this->user->id,
                'status' => 'draft',
            ]);
            $article->save();

            $this->mockNeo4j->shouldNotReceive('syncArticle');

            $this->observer->restored($article);
        });
    });
});
