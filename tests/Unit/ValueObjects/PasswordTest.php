<?php

use App\ValueObjects\Password;

const TEST_HASH = '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewSBcbdIboPATSU.';
const VALID_PASSWORD = 'ValidPass123!';
const SECURE_PASSWORD = 'mySecureP@ssw0rd123';

describe('Password Value Object', function (): void {
    it('creates password from plain text', function (): void {
        $password = Password::fromPlainText(SECURE_PASSWORD);

        expect($password->getHashedValue())->toBeString()
            ->and(strlen($password->getHashedValue()))->toBeGreaterThan(10);
    });

    it('creates password from hash', function (): void {
        $hash = TEST_HASH;
        $password = Password::fromHash($hash);

        expect($password->getHashedValue())->toBe($hash);
    });

    it('verifies correct password', function (): void {
        $plainText = SECURE_PASSWORD;
        $password = Password::fromPlainText($plainText);

        expect($password->verify($plainText))->toBeTrue()
            ->and($password->verify('wrongPassword'))->toBeFalse();
    });

    it('throws exception for empty plain password', function (): void {
        Password::fromPlainText('');
    })->throws(InvalidArgumentException::class, 'Password cannot be empty');

    it('throws exception for empty hash', function (): void {
        Password::fromHash('');
    })->throws(InvalidArgumentException::class, 'Hashed password cannot be empty');

    it('validates valid password strength', function (): void {
        $password = Password::fromPlainText(VALID_PASSWORD);
        expect($password->getHashedValue())->toBeString();
    });

    it('throws exception for weak passwords', function (string $invalidPassword): void {
        expect(fn (): Password => Password::fromPlainText($invalidPassword))
            ->toThrow(InvalidArgumentException::class);
    })->with([
        'weak',
        'NoNumbers!',
        'nonumber123',
        'NOLOWER123!',
        '',
    ]);

    it('allows password without special characters', function (): void {
        $password = Password::fromPlainText('NoSpecial123');
        expect($password->getHashedValue())->toBeString();
    });

    it('returns string representation', function (): void {
        $password = Password::fromPlainText(SECURE_PASSWORD);

        expect($password->__toString())->toBe($password->getHashedValue());
    });

    it('throws exception for zero string in plain text', function (): void {
        expect(fn (): Password => Password::fromPlainText('0'))
            ->toThrow(InvalidArgumentException::class, 'Password cannot be empty');
    });

    it('throws exception for zero string in hash', function (): void {
        expect(fn (): Password => Password::fromHash('0'))
            ->toThrow(InvalidArgumentException::class, 'Hashed password cannot be empty');
    });

    it('throws exception for password too short', function (): void {
        expect(fn (): Password => Password::fromPlainText('Short1'))
            ->toThrow(InvalidArgumentException::class, 'Password must be at least 8 characters long');
    });

    it('throws exception for password too long', function (): void {
        $longPassword = VALID_PASSWORD . str_repeat('a', 250);

        expect(fn (): Password => Password::fromPlainText($longPassword))
            ->toThrow(InvalidArgumentException::class, 'Password cannot be longer than 255 characters');
    });

    it('throws exception for password without uppercase', function (): void {
        expect(fn (): Password => Password::fromPlainText('nouppercasehere123'))
            ->toThrow(InvalidArgumentException::class, 'Password must contain at least one uppercase letter');
    });

    it('throws exception for password without lowercase', function (): void {
        expect(fn (): Password => Password::fromPlainText('NOLOWERCASEHERE123'))
            ->toThrow(InvalidArgumentException::class, 'Password must contain at least one lowercase letter');
    });

    it('throws exception for password without numbers', function (): void {
        expect(fn (): Password => Password::fromPlainText('NoNumbersHere'))
            ->toThrow(InvalidArgumentException::class, 'Password must contain at least one number');
    });

    it('creates password using constructor', function (): void {
        $hash = TEST_HASH;
        $password = new Password($hash);

        expect($password->getHashedValue())->toBe($hash);
    });

    it('implements Stringable interface', function (): void {
        $password = Password::fromPlainText('TestPass123');

        expect($password)->toBeInstanceOf(Stringable::class);
    });

    it('checks equality of passwords', function (): void {
        $hash = TEST_HASH;
        $password1 = Password::fromHash($hash);
        $password2 = Password::fromHash($hash);
        $password3 = Password::fromHash('$2y$12$different');

        expect($password1->equals($password2))->toBeTrue()
            ->and($password1->equals($password3))->toBeFalse();
    });

    it('handles minimum valid password', function (): void {
        $minPassword = 'Aa1bbbbb';
        $password = Password::fromPlainText($minPassword);

        expect($password->verify($minPassword))->toBeTrue();
    });

    it('handles maximum valid password length', function (): void {
        $maxPassword = VALID_PASSWORD . str_repeat('a', 241);
        $password = Password::fromPlainText($maxPassword);

        expect($password->verify($maxPassword))->toBeTrue();
    });
});
