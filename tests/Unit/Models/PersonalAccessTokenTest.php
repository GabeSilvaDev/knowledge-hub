<?php

use App\Models\PersonalAccessToken;
use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;
use MongoDB\BSON\ObjectId;
use MongoDB\Laravel\Eloquent\DocumentModel;

describe('PersonalAccessToken Model Basic Functionality', function (): void {
    it('can instantiate personal access token model', function (): void {
        $token = new PersonalAccessToken;

        expect($token)->toBeInstanceOf(PersonalAccessToken::class)
            ->and($token)->toBeInstanceOf(SanctumPersonalAccessToken::class);
    });

    it('can set and get attributes', function (): void {
        $token = new PersonalAccessToken;
        $token->name = 'API Token';
        $token->token = hash('sha256', 'test-token');

        expect($token->name)->toBe('API Token')
            ->and($token->token)->toBe(hash('sha256', 'test-token'));
    });
});

describe('PersonalAccessToken Model Configuration', function (): void {
    it('uses mongodb connection', function (): void {
        $token = new PersonalAccessToken;

        expect($token->getConnectionName())->toBe('mongodb');
    });

    it('uses personal_access_tokens collection', function (): void {
        $token = new PersonalAccessToken;

        expect($token->getTable())->toBe('personal_access_tokens');
    });

    it('has _id as primary key', function (): void {
        $token = new PersonalAccessToken;

        expect($token->getKeyName())->toBe('_id');
    });

    it('has non-incrementing primary key', function (): void {
        $token = new PersonalAccessToken;

        expect($token->incrementing)->toBeFalse();
    });

    it('has string key type', function (): void {
        $token = new PersonalAccessToken;

        expect($token->getKeyType())->toBe('string');
    });

    it('uses DocumentModel trait', function (): void {
        $traits = class_uses(PersonalAccessToken::class);

        expect($traits)->toHaveKey(DocumentModel::class);
    });
});

describe('PersonalAccessToken Model Primary Key Handling', function (): void {
    it('getKey returns string when key is ObjectId', function (): void {
        $token = new PersonalAccessToken;
        $objectId = new ObjectId;

        $token->_id = $objectId;

        $key = $token->getKey();

        expect($key)->toBeString()
            ->and(strlen($key))->toBe(24);
    });

    it('getKey returns original value when key is not ObjectId', function (): void {
        $token = new PersonalAccessToken;

        $token->_id = '507f1f77bcf86cd799439011';

        $key = $token->getKey();

        expect($key)->toBe('507f1f77bcf86cd799439011')
            ->and($key)->toBeString();
    });

    it('getKey handles null key gracefully', function (): void {
        $token = new PersonalAccessToken;

        $key = $token->getKey();

        expect($key)->toBeNull();
    });

    it('getKey converts non-ObjectId integer to string', function (): void {
        $token = new PersonalAccessToken;

        $token->_id = 12345;

        $key = $token->getKey();

        expect($key)->toBe('12345')
            ->and($key)->toBeString();
    });

    it('getKey returns null for non-stringable objects', function (): void {
        $token = new PersonalAccessToken;
        $token->_id = new stdClass;

        $key = $token->getKey();

        expect($key)->toBeNull();
    });
});
