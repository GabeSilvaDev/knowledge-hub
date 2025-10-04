<?php

use App\ValueObjects\UserId;

describe('UserId Value Object', function (): void {
    it('creates user id with valid string', function (): void {
        $validId = '507f1f77bcf86cd799439011';
        $userId = UserId::from($validId);

        expect($userId->getValue())->toBe($validId)
            ->and((string) $userId)->toBe($validId);
    });

    it('throws exception for empty user id', function (): void {
        expect(fn (): UserId => UserId::from(''))
            ->toThrow(InvalidArgumentException::class, 'User ID cannot be empty');
    });

    it('throws exception for invalid user id format', function ($invalidId): void {
        expect(fn (): UserId => UserId::from($invalidId))
            ->toThrow(InvalidArgumentException::class, 'Invalid User ID format');
    })->with([
        'invalid-id',
        '123',
        'not-an-object-id',
        '507f1f77bcf86cd79943901',
        '507f1f77bcf86cd799439011z',
    ]);

    it('allows valid user id formats', function ($validId): void {
        $userId = UserId::from($validId);
        expect($userId->getValue())->toBe($validId);
    })->with([
        '507f1f77bcf86cd799439011',
        '507F1F77BCF86CD799439011',
        '123456789012345678901234',
        'aaaaaaaaaaaaaaaaaaaaaaaa',
    ]);

    it('generates valid user id', function (): void {
        $userId = UserId::generate();

        expect($userId->getValue())->toMatch('/^[a-f\d]{24}$/i');
    });

    it('generates unique user ids', function (): void {
        $userId1 = UserId::generate();
        $userId2 = UserId::generate();

        expect($userId1->getValue())->not->toBe($userId2->getValue());
    });

    it('checks if user id equals another user id', function (): void {
        $id = '507f1f77bcf86cd799439011';
        $userId1 = UserId::from($id);
        $userId2 = UserId::from($id);
        $userId3 = UserId::from('507f1f77bcf86cd799439012');

        expect($userId1->equals($userId2))->toBeTrue()
            ->and($userId1->equals($userId3))->toBeFalse();
    });
});
