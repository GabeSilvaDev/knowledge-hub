<?php

use App\Models\User;
use App\Services\UserService;

beforeEach(function (): void {
    User::query()->delete();

    $this->userService = app(UserService::class);
});

describe('UserService Unit Tests', function (): void {
    it('gets user by email', function (): void {
        $user = User::factory()->create(['email' => 'test@example.com']);

        $foundUser = $this->userService->getUserByEmail('test@example.com');

        expect($foundUser)->not->toBeNull()
            ->and($foundUser->id)->toBe($user->id);
    });

    it('returns null when user not found by email', function (): void {
        $foundUser = $this->userService->getUserByEmail('nonexistent@example.com');

        expect($foundUser)->toBeNull();
    });

    it('updates user profile', function (): void {
        $user = User::factory()->create([
            'name' => 'Old Name',
            'bio' => 'Old Bio',
        ]);

        $updatedUser = $this->userService->updateUser($user, [
            'name' => 'New Name',
            'bio' => 'New Bio',
        ]);

        expect($updatedUser->name)->toBe('New Name')
            ->and($updatedUser->bio)->toBe('New Bio');
    });
});
