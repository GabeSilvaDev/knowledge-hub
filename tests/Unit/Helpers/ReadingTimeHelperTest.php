<?php

use App\Helpers\ReadingTimeHelper;

const WORD_UNIT = 'word ';
const ONE_MINUTE_TEXT = '1 minuto';

describe('ReadingTimeHelper', function (): void {
    describe('calculate method', function (): void {
        it('calculates reading time for empty content', function (): void {
            expect(ReadingTimeHelper::calculate(''))->toBe(1);
        });

        it('calculates reading time for short content', function (): void {
            $content = 'This is a test with ten words for calculation.';
            expect(ReadingTimeHelper::calculate($content))->toBe(1);
        });

        it('calculates reading time for medium content', function (): void {
            $content = str_repeat(WORD_UNIT, 400);
            expect(ReadingTimeHelper::calculate($content))->toBe(2);
        });

        it('calculates reading time for long content', function (): void {
            $content = str_repeat(WORD_UNIT, 1000);
            expect(ReadingTimeHelper::calculate($content))->toBe(5);
        });

        it('calculates reading time with custom words per minute', function (): void {
            $content = str_repeat(WORD_UNIT, 600);
            expect(ReadingTimeHelper::calculate($content, 300))->toBe(2);
        });

        it('strips HTML tags before calculating', function (): void {
            $content = '<p>This is <strong>a test</strong> with <em>ten words</em> for calculation.</p>';
            expect(ReadingTimeHelper::calculate($content))->toBe(1);
        });

        it('handles complex HTML content', function (): void {
            $content = '<div><h1>Title</h1><p>Paragraph with <a href="#">link</a> and <img src="test.jpg" alt="image"> content.</p></div>';
            expect(ReadingTimeHelper::calculate($content))->toBe(1);
        });

        it('returns minimum 1 minute for any content', function (): void {
            expect(ReadingTimeHelper::calculate('word'))->toBe(1)
                ->and(ReadingTimeHelper::calculate('one two'))->toBe(1);
        });

        it('handles content with multiple spaces and newlines', function (): void {
            $content = "Word1    word2\n\nword3\t\tword4   word5";
            expect(ReadingTimeHelper::calculate($content))->toBe(1);
        });

        it('calculates correctly for exact word boundaries', function (): void {
            $content = str_repeat(WORD_UNIT, 200);
            expect(ReadingTimeHelper::calculate($content, 200))->toBe(1);

            $content = str_repeat(WORD_UNIT, 201);
            expect(ReadingTimeHelper::calculate($content, 200))->toBe(2);
        });
    });
});
