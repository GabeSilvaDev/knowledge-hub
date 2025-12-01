<?php

use App\Models\Article;
use App\Models\Follower;
use App\Models\User;
use Illuminate\Support\Facades\Redis;

use function Pest\Laravel\artisan;

beforeEach(function (): void {
    Redis::del('users:ranking:influence');
});

describe('users:sync-ranking command', function (): void {
    it('syncs user ranking from database', function (): void {
        $user = User::factory()->create();

        Article::factory()->create([
            'author_id' => $user->id,
            'status' => 'published',
            'view_count' => 100,
        ]);

        artisan('users:sync-ranking')
            ->expectsOutput('Sincronizando ranking de usuários...')
            ->expectsOutput('Ranking sincronizado com sucesso!')
            ->assertSuccessful();
    });

    it('displays statistics after sync', function (): void {
        User::factory()->count(3)->create();

        artisan('users:sync-ranking')
            ->assertSuccessful();
    });

    it('handles empty database', function (): void {
        artisan('users:sync-ranking')
            ->expectsOutput('Sincronizando ranking de usuários...')
            ->expectsOutput('Ranking sincronizado com sucesso!')
            ->assertSuccessful();
    });

    it('calculates scores for users with articles', function (): void {
        $user = User::factory()->create();

        Article::factory()->count(2)->create([
            'author_id' => $user->id,
            'status' => 'published',
            'view_count' => 50,
            'like_count' => 5,
            'comment_count' => 2,
        ]);

        Follower::factory()->count(3)->create([
            'following_id' => $user->id,
        ]);

        artisan('users:sync-ranking')
            ->assertSuccessful();

        $score = Redis::zscore('users:ranking:influence', (string) $user->id);
        expect($score)->toBeGreaterThan(0);
    });
});
