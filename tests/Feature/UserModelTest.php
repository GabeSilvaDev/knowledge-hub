<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    User::truncate();
});

test('user can be created with valid data', function () {
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

test('user factory creates valid user', function () {
    $user = User::factory()->create();

    expect($user)->toBeInstanceOf(User::class)
        ->and($user->name)->toBeString()
        ->and($user->email)->toBeString()
        ->and($user->password)->toBeString();
});

test('user email can have multiple users in mongodb', function () {
    $user1 = User::factory()->create(['email' => 'test@example.com']);
    
    expect($user1->email)->toBe('test@example.com');
    
    $user2 = User::factory()->create(['email' => 'test2@example.com']);
    expect($user2->email)->toBe('test2@example.com');
});

test('user password is automatically hashed', function () {
    $plainPassword = 'secret123';
    
    $user = User::factory()->create([
        'password' => $plainPassword
    ]);

    expect($user->password)->not->toBe($plainPassword)
        ->and(Hash::check($plainPassword, $user->password))->toBeTrue();
});

test('user hidden attributes are not visible in array', function () {
    $user = User::factory()->create();
    $userArray = $user->toArray();

    expect($userArray)->not->toHaveKey('password')
        ->and($userArray)->not->toHaveKey('remember_token')
        ->and($userArray)->toHaveKey('name')
        ->and($userArray)->toHaveKey('email');
});

test('user fillable attributes can be mass assigned', function () {
    $userData = [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => 'secret123',
    ];

    $user = User::create($userData);

    expect($user->name)->toBe('Jane Doe')
        ->and($user->email)->toBe('jane@example.com');
});

test('user can be found by email', function () {
    $email = 'findme@example.com';
    $user = User::factory()->create(['email' => $email]);
    
    $foundUser = User::where('email', $email)->first();
    
    expect($foundUser)->not->toBeNull()
        ->and($foundUser->email)->toBe($email)
        ->and((string) $foundUser->_id)->toBe((string) $user->_id);
});

test('user count increases when user is created', function () {
    $initialCount = User::count();
    
    User::factory()->create();
    
    expect(User::count())->toBe($initialCount + 1);
});

test('user can be updated', function () {
    $user = User::factory()->create(['name' => 'Old Name']);
    
    $user->update(['name' => 'New Name']);
    
    expect($user->fresh()->name)->toBe('New Name');
});

test('user can be deleted', function () {
    $user = User::factory()->create();
    $userId = $user->_id;
    
    $user->delete();
    
    expect(User::find($userId))->toBeNull();
});
