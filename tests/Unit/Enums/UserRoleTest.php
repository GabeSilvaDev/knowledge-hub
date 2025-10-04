<?php

use App\Enums\UserRole;

describe('UserRole Enum', function (): void {
    it('has all expected role cases', function (): void {
        $cases = UserRole::cases();

        expect($cases)->toHaveCount(4)
            ->and($cases[0])->toBe(UserRole::ADMIN)
            ->and($cases[1])->toBe(UserRole::AUTHOR)
            ->and($cases[2])->toBe(UserRole::MODERATOR)
            ->and($cases[3])->toBe(UserRole::READER);
    });

    it('has correct string values', function (): void {
        expect(UserRole::ADMIN->value)->toBe('admin')
            ->and(UserRole::AUTHOR->value)->toBe('author')
            ->and(UserRole::MODERATOR->value)->toBe('moderator')
            ->and(UserRole::READER->value)->toBe('reader');
    });

    it('returns all values', function (): void {
        $values = UserRole::values();

        expect($values)->toBe(['admin', 'author', 'moderator', 'reader'])
            ->and($values)->toHaveCount(4);
    });

    it('can be created from string value', function (): void {
        expect(UserRole::from('admin'))->toBe(UserRole::ADMIN)
            ->and(UserRole::from('author'))->toBe(UserRole::AUTHOR)
            ->and(UserRole::from('moderator'))->toBe(UserRole::MODERATOR)
            ->and(UserRole::from('reader'))->toBe(UserRole::READER);
    });

    it('throws exception for invalid value', function (): void {
        expect(fn () => UserRole::from('invalid'))
            ->toThrow(ValueError::class);
    });

    it('returns correct labels', function (): void {
        expect(UserRole::ADMIN->label())->toBe('Administrador')
            ->and(UserRole::AUTHOR->label())->toBe('Autor')
            ->and(UserRole::MODERATOR->label())->toBe('Moderador')
            ->and(UserRole::READER->label())->toBe('Leitor');
    });

    it('checks write permissions correctly', function (): void {
        expect(UserRole::ADMIN->canWrite())->toBeTrue()
            ->and(UserRole::AUTHOR->canWrite())->toBeTrue()
            ->and(UserRole::MODERATOR->canWrite())->toBeTrue()
            ->and(UserRole::READER->canWrite())->toBeFalse();
    });

    it('checks moderation permissions correctly', function (): void {
        expect(UserRole::ADMIN->canModerate())->toBeTrue()
            ->and(UserRole::MODERATOR->canModerate())->toBeTrue()
            ->and(UserRole::AUTHOR->canModerate())->toBeFalse()
            ->and(UserRole::READER->canModerate())->toBeFalse();
    });

    it('checks admin permissions correctly', function (): void {
        expect(UserRole::ADMIN->canAdmin())->toBeTrue()
            ->and(UserRole::AUTHOR->canAdmin())->toBeFalse()
            ->and(UserRole::MODERATOR->canAdmin())->toBeFalse()
            ->and(UserRole::READER->canAdmin())->toBeFalse();
    });

    it('can be used in match expressions', function (): void {
        $getDescription = (fn (UserRole $role): string => match ($role) {
            UserRole::ADMIN => 'Full system access',
            UserRole::AUTHOR => 'Can create content',
            UserRole::MODERATOR => 'Can moderate content',
            UserRole::READER => 'Read-only access',
        });

        expect($getDescription(UserRole::ADMIN))->toBe('Full system access')
            ->and($getDescription(UserRole::AUTHOR))->toBe('Can create content')
            ->and($getDescription(UserRole::MODERATOR))->toBe('Can moderate content')
            ->and($getDescription(UserRole::READER))->toBe('Read-only access');
    });

    it('can be compared for equality', function (): void {
        $admin1 = UserRole::ADMIN;
        $admin2 = UserRole::ADMIN;

        expect($admin1 === $admin2)->toBeTrue()
            ->and(UserRole::ADMIN === UserRole::AUTHOR)->toBeFalse()
            ->and(UserRole::MODERATOR !== UserRole::READER)->toBeTrue();
    });

    it('can use tryFrom for safe creation', function (): void {
        expect(UserRole::tryFrom('admin'))->toBe(UserRole::ADMIN)
            ->and(UserRole::tryFrom('author'))->toBe(UserRole::AUTHOR)
            ->and(UserRole::tryFrom('invalid'))->toBeNull()
            ->and(UserRole::tryFrom(''))->toBeNull();
    });

    it('works correctly in arrays and collections', function (): void {
        $roles = [UserRole::ADMIN, UserRole::AUTHOR];

        expect(in_array(UserRole::ADMIN, $roles))->toBeTrue()
            ->and(in_array(UserRole::READER, $roles))->toBeFalse()
            ->and(count($roles))->toBe(2);
    });

    it('can be serialized to string', function (): void {
        expect((string) UserRole::ADMIN->value)->toBe('admin')
            ->and((string) UserRole::AUTHOR->value)->toBe('author')
            ->and((string) UserRole::MODERATOR->value)->toBe('moderator');
    });

    it('supports filtering by permissions', function (): void {
        $allRoles = UserRole::cases();
        $writersRoles = array_filter($allRoles, fn (UserRole $role): bool => $role->canWrite());
        $moderatorsRoles = array_filter($allRoles, fn (UserRole $role): bool => $role->canModerate());
        $adminRoles = array_filter($allRoles, fn (UserRole $role): bool => $role->canAdmin());

        expect($writersRoles)->toHaveCount(3)
            ->and($moderatorsRoles)->toHaveCount(2)
            ->and($adminRoles)->toHaveCount(1)
            ->and(in_array(UserRole::ADMIN, $writersRoles))->toBeTrue()
            ->and(in_array(UserRole::READER, $writersRoles))->toBeFalse()
            ->and(in_array(UserRole::ADMIN, $moderatorsRoles))->toBeTrue()
            ->and(in_array(UserRole::AUTHOR, $moderatorsRoles))->toBeFalse()
            ->and(in_array(UserRole::ADMIN, $adminRoles))->toBeTrue()
            ->and(in_array(UserRole::MODERATOR, $adminRoles))->toBeFalse();
    });

    it('validates role hierarchy', function (): void {
        expect(UserRole::ADMIN->canAdmin())->toBeTrue()
            ->and(UserRole::ADMIN->canModerate())->toBeTrue()
            ->and(UserRole::ADMIN->canWrite())->toBeTrue()
            ->and(UserRole::MODERATOR->canAdmin())->toBeFalse()
            ->and(UserRole::MODERATOR->canModerate())->toBeTrue()
            ->and(UserRole::MODERATOR->canWrite())->toBeTrue()
            ->and(UserRole::AUTHOR->canAdmin())->toBeFalse()
            ->and(UserRole::AUTHOR->canModerate())->toBeFalse()
            ->and(UserRole::AUTHOR->canWrite())->toBeTrue()
            ->and(UserRole::READER->canAdmin())->toBeFalse()
            ->and(UserRole::READER->canModerate())->toBeFalse()
            ->and(UserRole::READER->canWrite())->toBeFalse();
    });

    it('can determine if role is privileged', function (): void {
        $isPrivileged = (fn (UserRole $role): bool => $role->canWrite() || $role->canModerate() || $role->canAdmin());

        expect($isPrivileged(UserRole::ADMIN))->toBeTrue()
            ->and($isPrivileged(UserRole::AUTHOR))->toBeTrue()
            ->and($isPrivileged(UserRole::MODERATOR))->toBeTrue()
            ->and($isPrivileged(UserRole::READER))->toBeFalse();
    });
});
