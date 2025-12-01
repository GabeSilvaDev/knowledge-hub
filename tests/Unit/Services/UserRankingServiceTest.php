<?php

use App\Contracts\ArticleRepositoryInterface;
use App\Contracts\FollowerRepositoryInterface;
use App\Contracts\UserRepositoryInterface;
use App\DTOs\UserRankingDTO;
use App\Models\Article;
use App\Models\User;
use App\Services\UserRankingService;
use Illuminate\Support\Facades\Redis;

beforeEach(function (): void {
    Redis::del('users:ranking:influence');

    $this->userRepository = Mockery::mock(UserRepositoryInterface::class);
    $this->followerRepository = Mockery::mock(FollowerRepositoryInterface::class);
    $this->articleRepository = Mockery::mock(ArticleRepositoryInterface::class);
    $this->service = new UserRankingService($this->userRepository, $this->followerRepository, $this->articleRepository);
});

it('updates score for user', function (): void {
    $this->service->updateScore('user-123', 150.5);

    $score = Redis::zscore('users:ranking:influence', 'user-123');
    expect($score)->toBe(150.5);
});

it('increments score for user', function (): void {
    $this->service->updateScore('user-456', 100.0);
    $this->service->incrementScore('user-456', 25.5);

    $score = Redis::zscore('users:ranking:influence', 'user-456');
    expect($score)->toBe(125.5);
});

it('increments score multiple times', function (): void {
    $this->service->incrementScore('user-789');
    $this->service->incrementScore('user-789', 3.0);
    $this->service->incrementScore('user-789', 2.0);

    $score = Redis::zscore('users:ranking:influence', 'user-789');
    expect($score)->toBe(6.0);
});

it('returns top users in descending order', function (): void {
    $this->service->updateScore('user-1', 100);
    $this->service->updateScore('user-2', 200);
    $this->service->updateScore('user-3', 150);

    $top = $this->service->getTopUsers(3);

    expect($top)->toHaveCount(3)
        ->and($top[0]['user_id'])->toBe('user-2')
        ->and($top[0]['score'])->toBe(200.0)
        ->and($top[1]['user_id'])->toBe('user-3')
        ->and($top[1]['score'])->toBe(150.0)
        ->and($top[2]['user_id'])->toBe('user-1')
        ->and($top[2]['score'])->toBe(100.0);
});

it('limits top users results', function (): void {
    for ($i = 1; $i <= 20; $i++) {
        $this->service->updateScore("user-{$i}", $i * 10);
    }

    $top = $this->service->getTopUsers(5);

    expect($top)->toHaveCount(5)
        ->and($top[0]['score'])->toBe(200.0)
        ->and($top[4]['score'])->toBe(160.0);
});

it('returns user rank position', function (): void {
    $this->service->updateScore('user-1', 100);
    $this->service->updateScore('user-2', 200);
    $this->service->updateScore('user-3', 150);

    $rank1 = $this->service->getUserRank('user-2');
    $rank2 = $this->service->getUserRank('user-3');
    $rank3 = $this->service->getUserRank('user-1');

    expect($rank1)->toBe(1)
        ->and($rank2)->toBe(2)
        ->and($rank3)->toBe(3);
});

it('returns null for non-existing user rank', function (): void {
    $rank = $this->service->getUserRank('non-existing');

    expect($rank)->toBeNull();
});

it('returns user score', function (): void {
    $this->service->updateScore('user-test', 42.5);

    $score = $this->service->getUserScore('user-test');

    expect($score)->toBe(42.5);
});

it('returns zero score for non-existing user', function (): void {
    $score = $this->service->getUserScore('non-existing');

    expect($score)->toBe(0.0);
});

it('removes user from ranking', function (): void {
    $this->service->updateScore('user-remove', 50);

    expect($this->service->getUserScore('user-remove'))->toBe(50.0);

    $this->service->removeUser('user-remove');

    expect($this->service->getUserScore('user-remove'))->toBe(0.0);
});

it('resets entire ranking', function (): void {
    $this->service->updateScore('user-1', 100);
    $this->service->updateScore('user-2', 200);
    $this->service->updateScore('user-3', 150);

    $this->service->resetRanking();

    $top = $this->service->getTopUsers(10);
    expect($top)->toHaveCount(0);
});

it('calculates statistics correctly', function (): void {
    $this->service->updateScore('user-1', 100);
    $this->service->updateScore('user-2', 50);
    $this->service->updateScore('user-3', 25);

    $stats = $this->service->getStatistics();

    expect($stats)->toBeArray()
        ->and($stats['total_users'])->toBe(3)
        ->and($stats['total_score'])->toBe(175.0)
        ->and($stats['top_score'])->toBe(100.0)
        ->and($stats['average_score'])->toBe(58.33);
});

it('returns zero statistics for empty ranking', function (): void {
    $stats = $this->service->getStatistics();

    expect($stats['total_users'])->toBe(0)
        ->and($stats['total_score'])->toBe(0.0)
        ->and($stats['top_score'])->toBe(0.0)
        ->and($stats['average_score'])->toBe(0.0);
});

it('calculates influence score correctly', function (): void {
    $user = User::factory()->create();

    $this->userRepository->shouldReceive('findById')
        ->with((string) $user->id)
        ->andReturn($user);

    $this->followerRepository->shouldReceive('getFollowerCount')
        ->with((string) $user->id)
        ->andReturn(5);

    $this->articleRepository->shouldReceive('getPublishedArticleStatsByAuthor')
        ->with((string) $user->id)
        ->andReturn([
            'articles_count' => 1,
            'total_views' => 100,
            'total_likes' => 10,
            'total_comments' => 5,
        ]);

    $score = $this->service->calculateInfluenceScore((string) $user->id);

    expect($score)->toBe(75.5);
});

it('returns zero for non-existing user influence', function (): void {
    $this->userRepository->shouldReceive('findById')
        ->with('non-existing')
        ->andReturn(null);

    $this->articleRepository->shouldReceive('getPublishedArticleStatsByAuthor')
        ->never();

    $score = $this->service->calculateInfluenceScore('non-existing');

    expect($score)->toBe(0.0);
});

it('syncs ranking from database', function (): void {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    Article::factory()->create([
        'author_id' => $user1->id,
        'status' => 'published',
        'view_count' => 200,
    ]);

    Article::factory()->create([
        'author_id' => $user2->id,
        'status' => 'published',
        'view_count' => 100,
    ]);

    $service = app(UserRankingService::class);
    $service->syncFromDatabase();

    $top = $service->getTopUsers(10);

    expect($top->count())->toBeGreaterThanOrEqual(2);
});

it('gets enriched top users with user details', function (): void {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $service = app(UserRankingService::class);
    $service->updateScore((string) $user1->id, 200);
    $service->updateScore((string) $user2->id, 100);

    $enriched = $service->getEnrichedTopUsers(2);

    expect($enriched)->toHaveCount(2)
        ->and($enriched[0]['rank'])->toBe(1)
        ->and($enriched[0]['user']['name'])->toBe($user1->name)
        ->and($enriched[1]['rank'])->toBe(2)
        ->and($enriched[1]['user']['name'])->toBe($user2->name);
});

it('gets enriched user ranking info', function (): void {
    $user = User::factory()->create(['name' => 'Test User']);

    Article::factory()->create([
        'author_id' => $user->id,
        'status' => 'published',
        'view_count' => 50,
        'like_count' => 5,
        'comment_count' => 2,
    ]);

    $service = app(UserRankingService::class);
    $service->updateScore((string) $user->id, 100);

    $ranking = $service->getEnrichedUserRanking((string) $user->id);

    expect($ranking)->toBeInstanceOf(UserRankingDTO::class)
        ->and($ranking->userId)->toBe((string) $user->id)
        ->and($ranking->score)->toBe(100.0)
        ->and($ranking->rank)->toBe(1)
        ->and($ranking->totalViews)->toBe(50)
        ->and($ranking->totalLikes)->toBe(5)
        ->and($ranking->totalComments)->toBe(2)
        ->and($ranking->articlesCount)->toBe(1)
        ->and($ranking->user['name'])->toBe('Test User');
});

it('returns empty ranking for non-existing user', function (): void {
    $this->userRepository->shouldReceive('findById')
        ->with('non-existing')
        ->andReturn(null);

    $this->articleRepository->shouldReceive('getPublishedArticleStatsByAuthor')
        ->with('non-existing')
        ->andReturn([
            'articles_count' => 0,
            'total_views' => 0,
            'total_likes' => 0,
            'total_comments' => 0,
        ]);

    $ranking = $this->service->getEnrichedUserRanking('non-existing');

    expect($ranking->userId)->toBe('non-existing')
        ->and($ranking->rank)->toBeNull()
        ->and($ranking->score)->toBe(0.0);
});

it('recalculates user ranking', function (): void {
    $user = User::factory()->create();

    Article::factory()->create([
        'author_id' => $user->id,
        'status' => 'published',
        'view_count' => 100,
        'like_count' => 10,
        'comment_count' => 5,
    ]);

    $service = app(UserRankingService::class);
    $service->recalculateUser((string) $user->id);

    $score = $service->getUserScore((string) $user->id);

    expect($score)->toBeGreaterThan(0);
});

it('handles ranking key expiration', function (): void {
    $this->service->updateScore('user-1', 10);

    $ttl = Redis::ttl('users:ranking:influence');

    expect($ttl)->toBeGreaterThan(7775000)
        ->and($ttl)->toBeLessThanOrEqual(7776000);
});
