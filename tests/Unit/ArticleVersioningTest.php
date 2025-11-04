<?php

use App\Models\Article;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

it('creates a version when article is updated', function (): void {
    $user = User::factory()->create();
    Auth::login($user);

    $article = Article::factory()->create([
        'title' => 'Original Title',
        'content' => 'Original Content',
    ]);

    expect($article->versions()->count())->toBe(0);

    $article->update(['title' => 'Updated Title']);

    expect($article->versions()->count())->toBe(1);

    $version = $article->getLatestVersion();
    expect($version)
        ->not->toBeNull()
        ->and($version->title)->toBe('Original Title')
        ->and($version->version_number)->toBe(1);
});

it('does not create version when non-versionable fields are updated', function (): void {
    $article = Article::factory()->create([
        'view_count' => 10,
    ]);

    $article->disableVersioning();
    $article->update(['view_count' => 20]);
    $article->enableVersioning();

    expect($article->versions()->count())->toBe(0);
});

it('can restore article to previous version', function (): void {
    $article = Article::factory()->create([
        'title' => 'Version 1',
        'content' => 'Content 1',
    ]);

    $article->update(['title' => 'Version 2', 'content' => 'Content 2']);
    $article->update(['title' => 'Version 3', 'content' => 'Content 3']);

    expect($article->versions()->count())->toBe(2);

    $restored = $article->restoreToVersion(1);

    expect($restored)->toBeTrue()
        ->and($article->fresh()->title)->toBe('Version 1')
        ->and($article->fresh()->content)->toBe('Content 1');
});

it('tracks changed fields in version', function (): void {
    $article = Article::factory()->create([
        'title' => 'Original Title',
        'content' => 'Original Content',
        'excerpt' => 'Original Excerpt',
    ]);

    $article->update([
        'title' => 'New Title',
        'content' => 'New Content',
    ]);

    $version = $article->getLatestVersion();

    expect($version->changed_fields)
        ->toContain('title')
        ->toContain('content')
        ->not->toContain('excerpt');
});

it('can compare two versions', function (): void {
    $article = Article::factory()->create([
        'title' => 'Version 1',
        'content' => 'Content 1',
    ]);

    $article->update(['title' => 'Version 2']);
    $article->update(['title' => 'Version 3', 'content' => 'Content 3']);

    $differences = $article->compareVersions(1, 2);

    expect($differences)
        ->toHaveKey('title')
        ->and($differences['title'])
        ->toHaveKey('version_1')
        ->toHaveKey('version_2');
});

it('increments version numbers correctly', function (): void {
    $article = Article::factory()->create(['title' => 'Original']);

    $article->update(['title' => 'Update 1']);
    $article->update(['title' => 'Update 2']);
    $article->update(['title' => 'Update 3']);

    expect($article->getVersionCount())->toBe(3);

    $versions = $article->versions()->orderBy('version_number')->get();

    expect($versions->pluck('version_number')->toArray())->toBe([1, 2, 3]);
});

it('stores version metadata correctly', function (): void {
    $user = User::factory()->create();
    Auth::login($user);

    $article = Article::factory()->create(['title' => 'Original']);

    $article->update(['title' => 'Updated']);

    $version = $article->getLatestVersion();

    expect($version)
        ->versioned_by->toBe($user->_id)
        ->and($version->article_id)->toBe($article->_id)
        ->and($version->version_number)->toBe(1);
});

it('can create manual version with reason', function (): void {
    $article = Article::factory()->create(['title' => 'Original']);

    $version = $article->createVersion('Manual backup before major changes');

    expect($version)
        ->version_reason->toBe('Manual backup before major changes')
        ->and($version->version_number)->toBe(1);
});

it('can disable and enable versioning', function (): void {
    $article = Article::factory()->create(['title' => 'Original']);

    $article->disableVersioning();
    $article->update(['title' => 'Update 1']);

    expect($article->versions()->count())->toBe(0);

    $article->enableVersioning();
    $article->update(['title' => 'Update 2']);

    expect($article->versions()->count())->toBe(1);
});

it('returns null for non-existent version', function (): void {
    $article = Article::factory()->create();

    expect($article->getVersion(999))->toBeNull();
});

it('returns false when restoring to non-existent version', function (): void {
    $article = Article::factory()->create();

    expect($article->restoreToVersion(999))->toBeFalse();
});

it('preserves all versionable fields in version snapshot', function (): void {
    $article = Article::factory()->create([
        'title' => 'Test Article',
        'slug' => 'test-article',
        'content' => 'Test Content',
        'excerpt' => 'Test Excerpt',
        'tags' => ['PHP', 'Laravel'],
        'categories' => ['Programming'],
        'status' => 'published',
        'type' => 'article',
    ]);

    $article->update(['title' => 'Updated Article']);

    $version = $article->getLatestVersion();

    expect($version)
        ->title->toBe('Test Article')
        ->slug->toBe('test-article')
        ->content->toBe('Test Content')
        ->excerpt->toBe('Test Excerpt')
        ->tags->toBe(['PHP', 'Laravel'])
        ->categories->toBe(['Programming'])
        ->status->toBe('published')
        ->type->toBe('article');
});

it('maintains version history after multiple updates', function (): void {
    $article = Article::factory()->create(['title' => 'V1']);

    $article->update(['title' => 'V2']);
    $article->update(['title' => 'V3']);
    $article->update(['title' => 'V4']);
    $article->update(['title' => 'V5']);

    expect($article->getVersionCount())->toBe(4);

    $v1 = $article->getVersion(1);
    $v2 = $article->getVersion(2);
    $v3 = $article->getVersion(3);
    $v4 = $article->getVersion(4);

    expect($v1->title)->toBe('V1')
        ->and($v2->title)->toBe('V2')
        ->and($v3->title)->toBe('V3')
        ->and($v4->title)->toBe('V4')
        ->and($article->title)->toBe('V5');
});

it('does not create version when no versionable fields are dirty', function (): void {
    $user = User::factory()->create();
    Auth::login($user);

    $article = Article::factory()->create(['title' => 'Original']);

    $article->save();

    expect($article->getVersionCount())->toBe(0);
});

it('returns empty array when comparing non-existent versions', function (): void {
    $article = Article::factory()->create(['title' => 'Original']);

    $article->update(['title' => 'Updated']);

    $diffs = $article->compareVersions(99, 100);

    expect($diffs)->toBeArray()->toBeEmpty();
});

it('returns empty array when comparing with one non-existent version', function (): void {
    $article = Article::factory()->create(['title' => 'V1']);

    $article->update(['title' => 'V2']);

    $diffs = $article->compareVersions(1, 99);

    expect($diffs)->toBeArray()->toBeEmpty();
});
