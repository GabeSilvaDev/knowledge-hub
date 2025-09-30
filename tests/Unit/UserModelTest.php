<?php

use App\Models\User;

test('user uses mongodb connection', function () {
    $user = new User();
    
    expect($user->getConnectionName())->toBe('mongodb');
});

test('user uses correct collection name', function () {
    $user = new User();
    
    expect($user->getTable())->toBe('users');
});

test('user has correct fillable attributes', function () {
    $user = new User();
    
    expect($user->getFillable())->toContain('name')
        ->and($user->getFillable())->toContain('email')
        ->and($user->getFillable())->toContain('password');
});

test('user has correct hidden attributes', function () {
    $user = new User();
    
    expect($user->getHidden())->toContain('password')
        ->and($user->getHidden())->toContain('remember_token');
});

test('user has correct casts', function () {
    $user = new User();
    $casts = $user->getCasts();
    
    expect($casts)->toHaveKey('email_verified_at')
        ->and($casts)->toHaveKey('password')
        ->and($casts['email_verified_at'])->toBe('datetime')
        ->and($casts['password'])->toBe('hashed');
});
