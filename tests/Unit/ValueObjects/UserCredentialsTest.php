<?php

use App\Enums\UserRole;
use App\ValueObjects\Email;
use App\ValueObjects\Password;
use App\ValueObjects\UserCredentials;

describe('UserCredentials Construction Tests', function (): void {
    describe('construction and creation', function (): void {
        it('creates user credentials with constructor', function (): void {
            $email = Email::from('test@example.com');
            $password = Password::fromPlainText('TestPassword123!');
            $roles = [UserRole::ADMIN, UserRole::AUTHOR];

            $credentials = new UserCredentials($email, $password, $roles);

            expect($credentials->getEmail())->toBe($email)
                ->and($credentials->getPassword())->toBe($password)
                ->and($credentials->getRoles())->toBe($roles);
        });

        it('creates user credentials using static factory method', function (): void {
            $email = Email::from('test@example.com');
            $password = Password::fromPlainText('TestPassword123!');
            $roles = [UserRole::MODERATOR];

            $credentials = UserCredentials::create($email, $password, $roles);

            expect($credentials)->toBeInstanceOf(UserCredentials::class)
                ->and($credentials->getEmail())->toBe($email)
                ->and($credentials->getPassword())->toBe($password)
                ->and($credentials->getRoles())->toBe($roles);
        });

        it('creates user credentials without roles', function (): void {
            $email = Email::from('test@example.com');
            $password = Password::fromPlainText('TestPassword123!');

            $credentials = new UserCredentials($email, $password);

            expect($credentials->getEmail())->toBe($email)
                ->and($credentials->getPassword())->toBe($password)
                ->and($credentials->getRoles())->toBe([]);
        });

        it('creates user credentials using factory without roles', function (): void {
            $email = Email::from('test@example.com');
            $password = Password::fromPlainText('TestPassword123!');

            $credentials = UserCredentials::create($email, $password);

            expect($credentials->getEmail())->toBe($email)
                ->and($credentials->getPassword())->toBe($password)
                ->and($credentials->getRoles())->toBe([]);
        });

        it('creates user credentials with empty roles array explicitly', function (): void {
            $email = Email::from('test@example.com');
            $password = Password::fromPlainText('TestPassword123!');

            $credentials = UserCredentials::create($email, $password, []);

            expect($credentials->getEmail())->toBe($email)
                ->and($credentials->getPassword())->toBe($password)
                ->and($credentials->getRoles())->toBe([]);
        });
    });

    describe('getter methods', function (): void {
        it('returns email correctly', function (): void {
            $email = Email::from('test@example.com');
            $password = Password::fromPlainText('TestPassword123!');
            $credentials = new UserCredentials($email, $password);

            expect($credentials->getEmail())->toBe($email)
                ->and($credentials->getEmail()->getValue())->toBe('test@example.com');
        });

        it('returns password correctly', function (): void {
            $email = Email::from('test@example.com');
            $password = Password::fromPlainText('TestPassword123!');
            $credentials = new UserCredentials($email, $password);

            expect($credentials->getPassword())->toBe($password);
        });

        it('returns roles correctly when set', function (): void {
            $email = Email::from('test@example.com');
            $password = Password::fromPlainText('TestPassword123!');
            $roles = [UserRole::ADMIN, UserRole::AUTHOR, UserRole::MODERATOR];
            $credentials = new UserCredentials($email, $password, $roles);

            expect($credentials->getRoles())->toBe($roles)
                ->and($credentials->getRoles())->toHaveCount(3);
        });

        it('returns empty array when no roles set', function (): void {
            $email = Email::from('test@example.com');
            $password = Password::fromPlainText('TestPassword123!');
            $credentials = new UserCredentials($email, $password);

            expect($credentials->getRoles())->toBe([])
                ->and($credentials->getRoles())->toHaveCount(0);
        });
    });
});

describe('UserCredentials Role Tests', function (): void {
    describe('role checking', function (): void {
        it('returns true when user has specific role', function (): void {
            $email = Email::from('test@example.com');
            $password = Password::fromPlainText('TestPassword123!');
            $roles = [UserRole::ADMIN, UserRole::AUTHOR];
            $credentials = new UserCredentials($email, $password, $roles);

            expect($credentials->hasRole(UserRole::ADMIN))->toBeTrue()
                ->and($credentials->hasRole(UserRole::AUTHOR))->toBeTrue();
        });

        it('returns false when user does not have specific role', function (): void {
            $email = Email::from('test@example.com');
            $password = Password::fromPlainText('TestPassword123!');
            $roles = [UserRole::ADMIN];
            $credentials = new UserCredentials($email, $password, $roles);

            expect($credentials->hasRole(UserRole::MODERATOR))->toBeFalse()
                ->and($credentials->hasRole(UserRole::READER))->toBeFalse();
        });

        it('returns false when user has no roles', function (): void {
            $email = Email::from('test@example.com');
            $password = Password::fromPlainText('TestPassword123!');
            $credentials = new UserCredentials($email, $password);

            expect($credentials->hasRole(UserRole::ADMIN))->toBeFalse()
                ->and($credentials->hasRole(UserRole::AUTHOR))->toBeFalse()
                ->and($credentials->hasRole(UserRole::MODERATOR))->toBeFalse()
                ->and($credentials->hasRole(UserRole::READER))->toBeFalse();
        });

        it('handles single role correctly', function (): void {
            $email = Email::from('test@example.com');
            $password = Password::fromPlainText('TestPassword123!');
            $roles = [UserRole::READER];
            $credentials = new UserCredentials($email, $password, $roles);

            expect($credentials->hasRole(UserRole::READER))->toBeTrue()
                ->and($credentials->hasRole(UserRole::ADMIN))->toBeFalse();
        });

        it('handles all roles correctly', function (): void {
            $email = Email::from('test@example.com');
            $password = Password::fromPlainText('TestPassword123!');
            $roles = [UserRole::ADMIN, UserRole::AUTHOR, UserRole::MODERATOR, UserRole::READER];
            $credentials = new UserCredentials($email, $password, $roles);

            expect($credentials->hasRole(UserRole::ADMIN))->toBeTrue()
                ->and($credentials->hasRole(UserRole::AUTHOR))->toBeTrue()
                ->and($credentials->hasRole(UserRole::MODERATOR))->toBeTrue()
                ->and($credentials->hasRole(UserRole::READER))->toBeTrue();
        });
    });
});

describe('UserCredentials Equality Tests', function (): void {
    describe('equality comparison', function (): void {
        it('returns true for identical credentials with roles', function (): void {
            $email = Email::from('test@example.com');
            $password = Password::fromPlainText('TestPassword123!');
            $roles = [UserRole::ADMIN, UserRole::AUTHOR];

            $credentials1 = new UserCredentials($email, $password, $roles);
            $credentials2 = new UserCredentials($email, $password, $roles);

            expect($credentials1->equals($credentials2))->toBeTrue();
        });

        it('returns true for identical credentials without roles', function (): void {
            $email = Email::from('test@example.com');
            $password = Password::fromPlainText('TestPassword123!');

            $credentials1 = new UserCredentials($email, $password);
            $credentials2 = new UserCredentials($email, $password);

            expect($credentials1->equals($credentials2))->toBeTrue();
        });

        it('returns false for different emails', function (): void {
            $email1 = Email::from('test@example.com');
            $email2 = Email::from('other@example.com');
            $password = Password::fromPlainText('TestPassword123!');
            $roles = [UserRole::ADMIN];

            $credentials1 = new UserCredentials($email1, $password, $roles);
            $credentials2 = new UserCredentials($email2, $password, $roles);

            expect($credentials1->equals($credentials2))->toBeFalse();
        });

        it('returns false for different passwords', function (): void {
            $email = Email::from('test@example.com');
            $password1 = Password::fromPlainText('TestPassword123!');
            $password2 = Password::fromPlainText('OtherPassword456!');
            $roles = [UserRole::ADMIN];

            $credentials1 = new UserCredentials($email, $password1, $roles);
            $credentials2 = new UserCredentials($email, $password2, $roles);

            expect($credentials1->equals($credentials2))->toBeFalse();
        });

        it('returns false for different roles', function (): void {
            $email = Email::from('test@example.com');
            $password = Password::fromPlainText('TestPassword123!');
            $roles1 = [UserRole::ADMIN];
            $roles2 = [UserRole::AUTHOR];

            $credentials1 = new UserCredentials($email, $password, $roles1);
            $credentials2 = new UserCredentials($email, $password, $roles2);

            expect($credentials1->equals($credentials2))->toBeFalse();
        });

        it('returns false when one has roles and other does not', function (): void {
            $email = Email::from('test@example.com');
            $password = Password::fromPlainText('TestPassword123!');
            $roles = [UserRole::ADMIN];

            $credentials1 = new UserCredentials($email, $password, $roles);
            $credentials2 = new UserCredentials($email, $password);

            expect($credentials1->equals($credentials2))->toBeFalse();
        });

        it('returns true for credentials with equivalent value objects', function (): void {
            $email = Email::from('test@example.com');
            $password = Password::fromPlainText('TestPassword123!');
            $roles = [UserRole::ADMIN, UserRole::AUTHOR];

            $credentials1 = new UserCredentials($email, $password, $roles);
            $credentials2 = new UserCredentials($email, $password, $roles);

            expect($credentials1->equals($credentials2))->toBeTrue();
        });
    });

    describe('readonly properties', function (): void {
        it('exposes email as public readonly property', function (): void {
            $email = Email::from('test@example.com');
            $password = Password::fromPlainText('TestPassword123!');
            $credentials = new UserCredentials($email, $password);

            expect($credentials->email)->toBe($email);
        });

        it('exposes password as public readonly property', function (): void {
            $email = Email::from('test@example.com');
            $password = Password::fromPlainText('TestPassword123!');
            $credentials = new UserCredentials($email, $password);

            expect($credentials->password)->toBe($password);
        });

        it('exposes roles as public readonly property', function (): void {
            $email = Email::from('test@example.com');
            $password = Password::fromPlainText('TestPassword123!');
            $roles = [UserRole::ADMIN, UserRole::AUTHOR];
            $credentials = new UserCredentials($email, $password, $roles);

            expect($credentials->roles)->toBe($roles);
        });

        it('exposes empty roles as public readonly property', function (): void {
            $email = Email::from('test@example.com');
            $password = Password::fromPlainText('TestPassword123!');
            $credentials = new UserCredentials($email, $password);

            expect($credentials->roles)->toBe([]);
        });
    });
});
