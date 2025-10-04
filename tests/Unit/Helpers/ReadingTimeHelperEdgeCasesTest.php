<?php

use App\Helpers\ReadingTimeHelper;

describe('ReadingTimeHelper Edge Cases', function (): void {
    it('handles content with only HTML tags', function (): void {
        $content = '<div><p></p><span></span></div>';
        expect(ReadingTimeHelper::calculate($content))->toBe(1);
    });

    it('handles content with numbers and special characters', function (): void {
        $content = 'Test 123 content with @#$ special characters!';
        expect(ReadingTimeHelper::calculate($content))->toBe(1);
    });

    it('handles very slow reading speed', function (): void {
        $content = str_repeat('word ', 10);
        expect(ReadingTimeHelper::calculate($content, 5))->toBe(2);
    });

    it('handles very fast reading speed', function (): void {
        $content = str_repeat('word ', 1000);
        expect(ReadingTimeHelper::calculate($content, 1000))->toBe(1);
    });

    it('handles malformed HTML', function (): void {
        $content = '<p>Test <strong>content without closing tag';
        expect(ReadingTimeHelper::calculate($content))->toBe(1);
    });
});
