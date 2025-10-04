<?php

use App\ValueObjects\Title;

describe('Title Value Object', function (): void {
    it('creates title with valid text', function (): void {
        $title = Title::from('My Article Title');

        expect($title->getValue())->toBe('My Article Title')
            ->and($title->__toString())->toBe('My Article Title');
    });

    it('throws exception for empty title', function (): void {
        Title::from('');
    })->throws(InvalidArgumentException::class, 'Title cannot be empty');

    it('throws exception for whitespace only title', function (): void {
        Title::from('   ');
    })->throws(InvalidArgumentException::class, 'Title cannot be empty');

    it('gets trimmed title', function (): void {
        $title = Title::from('  My Title  ');

        expect($title->getTrimmed())->toBe('My Title');
    });

    it('checks if title equals another title', function (): void {
        $title1 = Title::from('Same Title');
        $title2 = Title::from('Same Title');
        $title3 = Title::from('Different Title');

        expect($title1->equals($title2))->toBeTrue()
            ->and($title1->equals($title3))->toBeFalse();
    });

    it('gets word count', function (): void {
        $title = Title::from('My Article Title');

        expect($title->getWordCount())->toBe(3);
    });

    it('gets character count', function (): void {
        $title = Title::from('Hello');

        expect(strlen($title->getValue()))->toBe(5);
    });

    it('validates title length', function (): void {
        expect(fn (): Title => Title::from('ab'))->toThrow(InvalidArgumentException::class, 'Title must be at least 3 characters long');
        expect(fn (): Title => Title::from(str_repeat('a', 256)))->toThrow(InvalidArgumentException::class, 'Title cannot be longer than 255 characters');
    });

    it('validates title contains no HTML tags', function (): void {
        expect(fn (): Title => Title::from('Title with <script>alert("xss")</script>'))->toThrow(InvalidArgumentException::class, 'Title cannot contain HTML tags');
        expect(fn (): Title => Title::from('Title with <b>bold</b>'))->toThrow(InvalidArgumentException::class, 'Title cannot contain HTML tags');
    });
});
