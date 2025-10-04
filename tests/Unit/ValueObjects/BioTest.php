<?php

use App\ValueObjects\Bio;

describe('Bio Value Object', function (): void {
    it('creates bio with valid text', function (): void {
        $bio = Bio::from('This is my bio');

        expect($bio->getValue())->toBe('This is my bio')
            ->and((string) $bio)->toBe('This is my bio');
    });

    it('creates bio with null value', function (): void {
        $bio = Bio::from(null);

        expect($bio->getValue())->toBeNull()
            ->and((string) $bio)->toBe('');
    });

    it('creates bio with empty string', function (): void {
        $bio = Bio::from('');

        expect($bio->getValue())->toBe('')
            ->and($bio->isEmpty())->toBeTrue();
    });

    it('throws exception for long bio', function (): void {
        $longBio = str_repeat('a', 501);
        expect(fn (): Bio => Bio::from($longBio))
            ->toThrow(InvalidArgumentException::class, 'Bio cannot be longer than 500 characters');
    });

    it('throws exception for prohibited content', function (): void {
        $maliciousBios = [
            '<script>alert("xss")</script>',
            'javascript:alert("xss")',
            '<img onclick="alert()" src="x">',
            'onload=alert()',
            '<div onmouseover="alert()">text</div>',
        ];

        foreach ($maliciousBios as $maliciousBio) {
            expect(fn (): Bio => Bio::from($maliciousBio))
                ->toThrow(InvalidArgumentException::class, 'Bio contains prohibited content');
        }
    });

    it('gets word count', function (): void {
        $bio = Bio::from('This is my bio description');
        expect($bio->getWordCount())->toBe(5);

        $emptyBio = Bio::from(null);
        expect($emptyBio->getWordCount())->toBe(0);
    });

    it('gets character count', function (): void {
        $bio = Bio::from('Hello');
        expect($bio->getCharacterCount())->toBe(5);

        $emptyBio = Bio::from(null);
        expect($emptyBio->getCharacterCount())->toBe(0);
    });

    it('checks if bio is empty', function (): void {
        $bio = Bio::from('Not empty');
        expect($bio->isEmpty())->toBeFalse();

        $emptyBio = Bio::from('');
        expect($emptyBio->isEmpty())->toBeTrue();

        $nullBio = Bio::from(null);
        expect($nullBio->isEmpty())->toBeTrue();

        $whitespaceBio = Bio::from('   ');
        expect($whitespaceBio->isEmpty())->toBeTrue();
    });

    it('checks if bio equals another bio', function (): void {
        $bio1 = Bio::from('Same bio');
        $bio2 = Bio::from('Same bio');
        $bio3 = Bio::from('Different bio');
        $bio4 = Bio::from(null);
        $bio5 = Bio::from(null);

        expect($bio1->equals($bio2))->toBeTrue()
            ->and($bio1->equals($bio3))->toBeFalse()
            ->and($bio4->equals($bio5))->toBeTrue();
    });
});
