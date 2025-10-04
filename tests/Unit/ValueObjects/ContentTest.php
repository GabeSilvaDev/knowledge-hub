<?php

use App\ValueObjects\Content;

describe('Content Value Object', function (): void {
    it('creates content with valid text', function (): void {
        $content = Content::from('This is my content');

        expect($content->getValue())->toBe('This is my content')
            ->and((string) $content)->toBe('This is my content');
    });

    it('throws exception for empty content', function (): void {
        expect(fn (): Content => Content::from(''))
            ->toThrow(InvalidArgumentException::class, 'Content cannot be empty');
    });

    it('throws exception for very long content', function (): void {
        $longContent = str_repeat('a', 1000001);
        expect(fn (): Content => Content::from($longContent))
            ->toThrow(InvalidArgumentException::class, 'Content is too long');
    });

    it('gets plain text from HTML content', function (): void {
        $content = Content::from('<p>This is <strong>HTML</strong> content</p>');

        expect($content->getPlainText())->toBe('This is HTML content');
    });

    it('gets word count', function (): void {
        $content = Content::from('This is a test content with words');

        expect($content->getWordCount())->toBe(7);
    });

    it('gets character count', function (): void {
        $content = Content::from('<p>Hello</p>');

        expect($content->getCharacterCount())->toBe(5);
    });

    it('gets excerpt with default length', function (): void {
        $longContent = str_repeat('This is content. ', 20);
        $content = Content::from($longContent);

        $excerpt = $content->getExcerpt();
        expect($excerpt)->toEndWith('...')
            ->and(strlen($excerpt))->toBeLessThanOrEqual(203);
    });

    it('gets excerpt with custom length', function (): void {
        $content = Content::from('This is a very long content that should be truncated');

        $excerpt = $content->getExcerpt(20);
        expect($excerpt)->toBe('This is a very long ...');
    });

    it('gets excerpt without truncation for short content', function (): void {
        $content = Content::from('Short content');

        $excerpt = $content->getExcerpt(50);
        expect($excerpt)->toBe('Short content');
    });

    it('calculates reading time', function (): void {
        $words = str_repeat('word ', 400);
        $content = Content::from($words);

        expect($content->getReadingTime())->toBe(2);
    });

    it('calculates reading time with custom words per minute', function (): void {
        $words = str_repeat('word ', 100);
        $content = Content::from($words);

        expect($content->getReadingTime(50))->toBe(2);
    });

    it('has minimum reading time of 1 minute', function (): void {
        $content = Content::from('Very short content');

        expect($content->getReadingTime())->toBe(1);
    });

    it('checks if content equals another content', function (): void {
        $content1 = Content::from('Same content text');
        $content2 = Content::from('Same content text');
        $content3 = Content::from('Different content');

        expect($content1->equals($content2))->toBeTrue()
            ->and($content1->equals($content3))->toBeFalse();
    });

    it('checks if content is empty (always false for valid content)', function (): void {
        $content1 = Content::from('Normal content');
        $content2 = Content::from('1');
        $content3 = Content::from('<p></p>');
        $content4 = Content::from('   single space   ');

        expect($content1->isEmpty())->toBeFalse()
            ->and($content2->isEmpty())->toBeFalse()
            ->and($content3->isEmpty())->toBeTrue()
            ->and($content4->isEmpty())->toBeFalse();
    });
});
