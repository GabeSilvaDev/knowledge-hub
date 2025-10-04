<?php

use App\ValueObjects\Name;

describe('Name Value Object', function (): void {
    it('creates name with valid text', function (): void {
        $name = Name::from('John Doe');

        expect($name->getValue())->toBe('John Doe');
        expect((string) $name)->toBe('John Doe');
    });

    it('throws exception for empty name', function (): void {
        expect(fn (): Name => Name::from(''))->toThrow(InvalidArgumentException::class, 'Name cannot be empty');
    });

    it('throws exception for short name', function (): void {
        expect(fn (): Name => Name::from('A'))->toThrow(InvalidArgumentException::class, 'Name must be at least 2 characters long');
    });

    it('throws exception for long name', function (): void {
        $longName = str_repeat('a', 101);
        expect(fn (): Name => Name::from($longName))->toThrow(InvalidArgumentException::class, 'Name cannot be longer than 100 characters');
    });

    it('throws exception for invalid characters', function (): void {
        expect(fn (): Name => Name::from('John123'))->toThrow(InvalidArgumentException::class, 'Name can only contain letters, spaces, apostrophes and hyphens');
        expect(fn (): Name => Name::from('John@Doe'))->toThrow(InvalidArgumentException::class, 'Name can only contain letters, spaces, apostrophes and hyphens');
    });

    it('allows valid characters', function (): void {
        expect(fn (): Name => Name::from("John O'Connor"))->not->toThrow(InvalidArgumentException::class);
        expect(fn (): Name => Name::from('Mary-Jane Smith'))->not->toThrow(InvalidArgumentException::class);
        expect(fn (): Name => Name::from('José Martínez'))->not->toThrow(InvalidArgumentException::class);
    });

    it('gets first name', function (): void {
        $name = Name::from('John Doe Smith');
        expect($name->getFirstName())->toBe('John');

        $singleName = Name::from('John');
        expect($singleName->getFirstName())->toBe('John');
    });

    it('gets last name', function (): void {
        $name = Name::from('John Doe Smith');
        expect($name->getLastName())->toBe('Smith');

        $singleName = Name::from('John');
        expect($singleName->getLastName())->toBe('');

        $twoNames = Name::from('John Doe');
        expect($twoNames->getLastName())->toBe('Doe');
    });

    it('gets initials', function (): void {
        $name = Name::from('John Doe Smith');
        expect($name->getInitials())->toBe('JDS');

        $singleName = Name::from('John');
        expect($singleName->getInitials())->toBe('J');

        $withSpaces = Name::from('John  Doe');
        expect($withSpaces->getInitials())->toBe('JD');
    });

    it('checks if name equals another name', function (): void {
        $name1 = Name::from('John Doe');
        $name2 = Name::from('John Doe');
        $name3 = Name::from('Jane Doe');

        expect($name1->equals($name2))->toBeTrue();
        expect($name1->equals($name3))->toBeFalse();
    });
});
