<?php

use App\Models\Article;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Redis;

beforeEach(function (): void {
    Redis::del('articles:ranking:views');
});

it('syncs article ranking from database via artisan command', function (): void {
    Article::query()->forceDelete();

    Article::factory()->create([
        'status' => 'published',
        'view_count' => 100,
    ]);

    Article::factory()->create([
        'status' => 'published',
        'view_count' => 50,
    ]);

    $exitCode = Artisan::call('articles:sync-ranking');

    expect($exitCode)->toBe(0);

    $output = Artisan::output();

    expect($output)->toContain('Sincronizando ranking')
        ->and($output)->toContain('sincronizado com sucesso')
        ->and($output)->toContain('Total de artigos')
        ->and($output)->toContain('Total de visualizações');
});

it('command shows correct statistics after sync', function (): void {
    Article::query()->forceDelete();

    Article::factory()->create([
        'status' => 'published',
        'view_count' => 100,
    ]);

    Artisan::call('articles:sync-ranking');

    $output = Artisan::output();

    expect($output)->toContain('100');
});

it('command handles empty database gracefully', function (): void {
    Article::query()->forceDelete();

    $exitCode = Artisan::call('articles:sync-ranking');

    expect($exitCode)->toBe(0);

    $output = Artisan::output();

    expect($output)->toContain('0');
});
