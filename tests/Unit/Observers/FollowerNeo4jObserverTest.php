<?php

use App\Contracts\Neo4jRepositoryInterface;
use App\Models\Follower;
use App\Models\User;
use App\Observers\FollowerNeo4jObserver;

beforeEach(function (): void {
    Follower::query()->delete();
    User::query()->delete();

    $this->mockNeo4j = Mockery::mock(Neo4jRepositoryInterface::class);
    $this->observer = new FollowerNeo4jObserver($this->mockNeo4j);
    $this->follower = User::factory()->create();
    $this->following = User::factory()->create();
});

afterEach(function (): void {
    Mockery::close();
});

describe('FollowerNeo4jObserver', function (): void {
    describe('created', function (): void {
        it('syncs follow relationship to Neo4j', function (): void {
            $followerRecord = Follower::create([
                'follower_id' => $this->follower->id,
                'following_id' => $this->following->id,
            ]);

            $this->mockNeo4j->shouldReceive('syncFollow')
                ->once()
                ->with((string) $this->follower->id, (string) $this->following->id);

            $this->observer->created($followerRecord);
        });
    });

    describe('deleted', function (): void {
        it('deletes follow relationship from Neo4j', function (): void {
            $followerRecord = Follower::create([
                'follower_id' => $this->follower->id,
                'following_id' => $this->following->id,
            ]);

            $this->mockNeo4j->shouldReceive('deleteFollow')
                ->once()
                ->with((string) $this->follower->id, (string) $this->following->id);

            $this->observer->deleted($followerRecord);
        });
    });
});
