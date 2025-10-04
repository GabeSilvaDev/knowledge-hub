<?php

use App\ValueObjects\Username;

const POSITION_ERROR_MESSAGE = 'Username cannot start or end with underscore or hyphen';

describe('Username Value Object', function (): void {
    it('creates username with valid text', function (): void {
        $username = Username::from('john_doe');

        expect($username->getValue())->toBe('john_doe')
            ->and((string) $username)->toBe('john_doe');
    });

    it('throws exception for empty username', function (): void {
        expect(fn (): Username => Username::from(''))
            ->toThrow(InvalidArgumentException::class, 'Username cannot be empty');
    });

    it('throws exception for short username', function (): void {
        expect(fn (): Username => Username::from('ab'))
            ->toThrow(InvalidArgumentException::class, 'Username must be at least 3 characters long');
    });

    it('throws exception for long username', function (): void {
        $longUsername = str_repeat('a', 51);
        expect(fn (): Username => Username::from($longUsername))
            ->toThrow(InvalidArgumentException::class, 'Username cannot be longer than 50 characters');
    });

    it('throws exception for invalid characters', function ($invalidUsername): void {
        expect(fn (): Username => Username::from($invalidUsername))
            ->toThrow(InvalidArgumentException::class, 'Username can only contain letters, numbers, underscores and hyphens');
    })->with([
        'user name',
        'user@name',
        'user.name',
        'user+name',
        'user#name',
    ]);

    it('allows valid usernames', function ($validUsername): void {
        $username = Username::from($validUsername);
        expect($username->getValue())->toBe($validUsername);
    })->with([
        'john_doe',
        'user123',
        'test-user',
        'MyUsername',
        'user_123',
        'test',
    ]);

    it('checks if username equals another username', function (): void {
        $username1 = Username::from('john_doe');
        $username2 = Username::from('john_doe');
        $username3 = Username::from('jane_doe');

        expect($username1->equals($username2))->toBeTrue()
            ->and($username1->equals($username3))->toBeFalse();
    });

    it('throws exception for username starting with underscore', function (): void {
        expect(fn (): Username => Username::from('_username'))
            ->toThrow(InvalidArgumentException::class, POSITION_ERROR_MESSAGE);
    });

    it('throws exception for username ending with underscore', function (): void {
        expect(fn (): Username => Username::from('username_'))
            ->toThrow(InvalidArgumentException::class, POSITION_ERROR_MESSAGE);
    });

    it('throws exception for username starting with hyphen', function (): void {
        expect(fn (): Username => Username::from('-username'))
            ->toThrow(InvalidArgumentException::class, POSITION_ERROR_MESSAGE);
    });

    it('throws exception for username ending with hyphen', function (): void {
        expect(fn (): Username => Username::from('username-'))
            ->toThrow(InvalidArgumentException::class, POSITION_ERROR_MESSAGE);
    });

    it('throws exception for zero string', function (): void {
        expect(fn (): Username => Username::from('0'))
            ->toThrow(InvalidArgumentException::class, 'Username cannot be empty');
    });

    it('creates username using constructor', function (): void {
        $username = new Username('testuser');

        expect($username->getValue())->toBe('testuser')
            ->and((string) $username)->toBe('testuser');
    });

    it('implements Stringable interface', function (): void {
        $username = Username::from('testuser');

        expect($username)->toBeInstanceOf(Stringable::class);
    });
});
