<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

beforeEach(function (): void {
    User::truncate();
});

it('user can be created with valid data', function (): void {
    $user = User::create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => Hash::make('password123'),
    ]);

    expect($user)->toBeInstanceOf(User::class)
        ->and($user->name)->toBe('John Doe')
        ->and($user->email)->toBe('john@example.com')
        ->and($user->password)->not->toBe('password123');
});

it('user factory creates valid user', function (): void {
    $user = User::factory()->create();

    expect($user)->toBeInstanceOf(User::class)
        ->and($user->name)->toBeString()
        ->and($user->email)->toBeString()
        ->and($user->password)->toBeString();
});

it('user email can have multiple users in mongodb', function (): void {
    $user1 = User::factory()->create(['email' => 'test@example.com']);

    expect($user1->email)->toBe('test@example.com');

    $user2 = User::factory()->create(['email' => 'test2@example.com']);
    expect($user2->email)->toBe('test2@example.com');
});

it('user password is automatically hashed', function (): void {
    $plainPassword = 'secret123';

    $user = User::factory()->create([
        'password' => $plainPassword,
    ]);

    expect($user->password)->not->toBe($plainPassword)
        ->and(Hash::check($plainPassword, $user->password))->toBeTrue();
});

it('user hidden attributes are not visible in array', function (): void {
    $user = User::factory()->create();
    $userArray = $user->toArray();

    expect($userArray)->not->toHaveKey('password')
        ->and($userArray)->not->toHaveKey('remember_token')
        ->and($userArray)->toHaveKey('name')
        ->and($userArray)->toHaveKey('email');
});

it('user fillable attributes can be mass assigned', function (): void {
    $userData = [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => 'secret123',
    ];

    $user = User::create($userData);

    expect($user->name)->toBe('Jane Doe')
        ->and($user->email)->toBe('jane@example.com');
});

it('user can be found by email', function (): void {
    $email = 'findme@example.com';
    $user = User::factory()->create(['email' => $email]);

    $foundUser = User::where('email', $email)->first();

    expect($foundUser)->not->toBeNull()
        ->and($foundUser->email)->toBe($email)
        ->and((string) $foundUser->_id)->toBe((string) $user->_id);
});

it('user count increases when user is created', function (): void {
    $initialCount = User::count();

    User::factory()->create();

    expect(User::count())->toBe($initialCount + 1);
});

it('user can be updated', function (): void {
    $user = User::factory()->create(['name' => 'Old Name']);

    $user->update(['name' => 'New Name']);

    expect($user->fresh()->name)->toBe('New Name');
});

it('user can be deleted', function (): void {
    $user = User::factory()->create();
    $userId = $user->_id;

    $user->delete();

    expect(User::find($userId))->toBeNull();
});
