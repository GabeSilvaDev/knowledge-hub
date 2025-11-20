<?php

use App\ValueObjects\Email;

const USER_EMAIL = 'user@example.com';

describe('Email Value Object', function (): void {
    it('creates email with valid address', function (): void {
        $email = Email::from(TEST_EMAIL);

        expect($email->getValue())->toBe(TEST_EMAIL)
            ->and($email->__toString())->toBe(TEST_EMAIL);
    });

    it('throws exception for empty email', function (): void {
        Email::from('');
    })->throws(InvalidArgumentException::class, 'Email cannot be empty');

    it('throws exception for invalid email format', function (): void {
        Email::from('invalid-email');
    })->throws(InvalidArgumentException::class, 'Invalid email format');

    it('preserves email case', function (): void {
        $email = Email::from('TEST@EXAMPLE.COM');

        expect($email->getValue())->toBe('TEST@EXAMPLE.COM');
    });

    it('checks if email equals another email', function (): void {
        $email1 = Email::from(TEST_EMAIL);
        $email2 = Email::from(TEST_EMAIL);
        $email3 = Email::from('other@example.com');

        expect($email1->equals($email2))->toBeTrue()
            ->and($email1->equals($email3))->toBeFalse();
    });

    it('gets domain from email', function (): void {
        $email = Email::from(USER_EMAIL);

        expect($email->getDomain())->toBe('example.com');
    });

    it('gets local part from email', function (): void {
        $email = Email::from(USER_EMAIL);

        expect($email->getLocalPart())->toBe('user');
    });

    it('validates valid email formats', function (string $validEmail): void {
        $email = Email::from($validEmail);
        expect($email->getValue())->toBe($validEmail);
    })->with([
        'valid@email.com',
        'test.email@domain.co.uk',
        'user+tag@example.org',
    ]);

    it('throws exception for invalid email formats', function (string $invalidEmail): void {
        expect(fn (): Email => Email::from($invalidEmail))
            ->toThrow(InvalidArgumentException::class);
    })->with([
        'invalid.email',
        '@domain.com',
        'user@',
        '',
    ]);

    it('throws exception for zero string', function (): void {
        expect(fn (): Email => Email::from('0'))
            ->toThrow(InvalidArgumentException::class, 'Email cannot be empty');
    });

    it('validates email at maximum length boundary', function (): void {
        $local = str_repeat('a', 64);
        $domain = str_repeat('example.', 23) . 'comxx';
        $email = $local . '@' . $domain;

        $emailObj = Email::from($email);
        expect($emailObj->getValue())->toBe($email);
        expect(strlen($email))->toBe(254);
    });

    it('throws exception for email that is too long', function (): void {
        $local = str_repeat('a', 64);
        $domain = str_repeat('b', 187) . '.com';
        $longEmail = $local . '@' . $domain;

        expect(strlen($longEmail))->toBeGreaterThan(254);
        expect(fn (): Email => Email::from($longEmail))
            ->toThrow(InvalidArgumentException::class, 'Email is too long');
    });

    it('creates email using constructor', function (): void {
        $email = new Email(TEST_EMAIL);

        expect($email->getValue())->toBe(TEST_EMAIL)
            ->and((string) $email)->toBe(TEST_EMAIL);
    });

    it('implements Stringable interface', function (): void {
        $email = Email::from(TEST_EMAIL);

        expect($email)->toBeInstanceOf(Stringable::class);
    });

    it('handles complex email addresses', function (): void {
        $complexEmail = 'user.name+tag@subdomain.example.co.uk';
        $email = Email::from($complexEmail);

        expect($email->getValue())->toBe($complexEmail)
            ->and($email->getDomain())->toBe('subdomain.example.co.uk')
            ->and($email->getLocalPart())->toBe('user.name+tag');
    });

    it('handles email with numbers', function (): void {
        $numericEmail = 'user123@domain456.com';
        $email = Email::from($numericEmail);

        expect($email->getValue())->toBe($numericEmail)
            ->and($email->getDomain())->toBe('domain456.com')
            ->and($email->getLocalPart())->toBe('user123');
    });

    it('handles moderately long valid email', function (): void {
        $longEmail = 'verylongusername.with.dots@verylongdomainname.example.com';

        $email = Email::from($longEmail);

        expect($email->getValue())->toBe($longEmail);
    });

    it('handles case sensitivity correctly', function (): void {
        $email1 = Email::from('Test@Example.Com');
        $email2 = Email::from('test@example.com');

        expect($email1->equals($email2))->toBeFalse()
            ->and($email1->getValue())->toBe('Test@Example.Com')
            ->and($email2->getValue())->toBe(TEST_EMAIL);
    });

    it('extracts domain from complex emails', function (): void {
        $email = Email::from('user@mail.subdomain.example.org');

        expect($email->getDomain())->toBe('mail.subdomain.example.org');
    });

    it('extracts local part from complex emails', function (): void {
        $email = Email::from('firstname.lastname+tag@example.com');

        expect($email->getLocalPart())->toBe('firstname.lastname+tag');
    });
});
