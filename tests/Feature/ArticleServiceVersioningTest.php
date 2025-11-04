<?php

use App\Models\Article;
use App\Models\User;
use App\Services\ArticleService;
use Illuminate\Support\Facades\Auth;

it('can create a manual version through service', function (): void {
    $service = app(ArticleService::class);
    $user = User::factory()->create();
    Auth::login($user);

    $article = Article::factory()->create(['title' => 'Original']);

    $version = $service->createArticleVersion($article, 'Backup before update');

    expect($version)
        ->version_reason->toBe('Backup before update')
        ->and($version->title)->toBe('Original');
});

it('can get all versions through service', function (): void {
    $service = app(ArticleService::class);

    $article = Article::factory()->create(['title' => 'V1']);

    $article->update(['title' => 'V2']);
    $article->update(['title' => 'V3']);

    $versions = $service->getArticleVersions($article);

    expect($versions)->toHaveCount(2);
});

it('can get specific version through service', function (): void {
    $service = app(ArticleService::class);

    $article = Article::factory()->create(['title' => 'V1']);

    $article->update(['title' => 'V2']);
    $article->update(['title' => 'V3']);

    $version = $service->getArticleVersion($article, 1);

    expect($version)
        ->not->toBeNull()
        ->title->toBe('V1');
});

it('can restore to version through service', function (): void {
    $service = app(ArticleService::class);

    $article = Article::factory()->create(['title' => 'V1', 'content' => 'Content 1']);

    $article->update(['title' => 'V2', 'content' => 'Content 2']);
    $article->update(['title' => 'V3', 'content' => 'Content 3']);

    $restored = $service->restoreArticleToVersion($article, 1);

    expect($restored)->toBeTrue()
        ->and($article->fresh()->title)->toBe('V1')
        ->and($article->fresh()->content)->toBe('Content 1');
});

it('can compare versions through service', function (): void {
    $service = app(ArticleService::class);

    $article = Article::factory()->create(['title' => 'V1']);

    $article->update(['title' => 'V2']);
    $article->update(['title' => 'V3']);

    $differences = $service->compareArticleVersions($article, 1, 2);

    expect($differences)->toHaveKey('title');
});

it('can get version count through service', function (): void {
    $service = app(ArticleService::class);

    $article = Article::factory()->create(['title' => 'V1']);

    $article->update(['title' => 'V2']);
    $article->update(['title' => 'V3']);

    $count = $service->getArticleVersionCount($article);

    expect($count)->toBe(2);
});

it('can update article without versioning through service', function (): void {
    $service = app(ArticleService::class);

    $article = Article::factory()->create(['title' => 'Original']);

    $service->updateArticleWithoutVersioning($article, ['title' => 'Updated']);

    expect($article->versions()->count())->toBe(0)
        ->and($article->fresh()->title)->toBe('Updated');
});

it('creates version when updating through service', function (): void {
    $service = app(ArticleService::class);

    $article = Article::factory()->create(['title' => 'Original']);

    $service->updateArticle($article, ['title' => 'Updated']);

    expect($article->versions()->count())->toBe(1)
        ->and($article->fresh()->title)->toBe('Updated');

    $version = $article->getLatestVersion();
    expect($version->title)->toBe('Original');
});
