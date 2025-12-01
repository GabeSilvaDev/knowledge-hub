<?php

use App\Contracts\Neo4jRepositoryInterface;
use App\Models\User;
use App\Observers\UserNeo4jObserver;

beforeEach(function (): void {
    User::query()->delete();

    $this->mockNeo4j = Mockery::mock(Neo4jRepositoryInterface::class);
    $this->observer = new UserNeo4jObserver($this->mockNeo4j);
});

afterEach(function (): void {
    Mockery::close();
});

describe('UserNeo4jObserver', function (): void {
    describe('created', function (): void {
        it('syncs user to Neo4j', function (): void {
            $user = User::factory()->make();
            $user->save();

            $this->mockNeo4j->shouldReceive('syncUser')
                ->once()
                ->with(Mockery::on(
                    fn ($data): bool => $data['id'] === $user->id
                    && $data['name'] === $user->name
                    && $data['email'] === $user->email
                    && $data['username'] === $user->username
                ));

            $this->observer->created($user);
        });
    });

    describe('updated', function (): void {
        it('syncs user on update', function (): void {
            $user = User::factory()->make();
            $user->save();

            $this->mockNeo4j->shouldReceive('syncUser')
                ->once()
                ->with(Mockery::on(fn ($data): bool => $data['id'] === $user->id));

            $this->observer->updated($user);
        });
    });

    describe('deleted', function (): void {
        it('deletes user from Neo4j', function (): void {
            $user = User::factory()->make();
            $user->save();

            $this->mockNeo4j->shouldReceive('deleteUser')
                ->once()
                ->with($user->id);

            $this->observer->deleted($user);
        });
    });
});
