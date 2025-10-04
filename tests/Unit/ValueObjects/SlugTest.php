<?php

use App\ValueObjects\Slug;

const SLUG_EMPTY_ERROR = 'Slug cannot be empty';

describe('Slug Value Object', function (): void {
    it('creates slug with valid text', function (): void {
        $slug = Slug::from('my-article-slug');

        expect($slug->getValue())->toBe('my-article-slug')
            ->and((string) $slug)->toBe('my-article-slug');
    });

    it('throws exception for empty slug', function (): void {
        expect(fn (): Slug => Slug::from(''))
            ->toThrow(InvalidArgumentException::class, SLUG_EMPTY_ERROR);
    });

    it('throws exception for invalid slug format', function ($invalidSlug): void {
        expect(fn (): Slug => Slug::from($invalidSlug))
            ->toThrow(InvalidArgumentException::class, 'Slug can only contain lowercase letters, numbers and hyphens');
    })->with([
        'Invalid Slug',
        'slug_with_underscore',
        'slug with spaces',
        'slug@with#symbols',
        'slug.',
    ]);

    it('allows valid slug formats', function ($validSlug): void {
        $slug = Slug::from($validSlug);
        expect($slug->getValue())->toBe($validSlug);
    })->with([
        'valid-slug',
        'slug123',
        'my-article-123',
        'simple',
        'long-slug-with-many-words',
    ]);

    it('creates slug from title', function (): void {
        $slug = Slug::fromTitle('My Article Title');

        expect($slug->getValue())->toBe('my-article-title');
    });

    it('creates slug from title with special characters', function (): void {
        $slug = Slug::fromTitle('Title with @#$% special chars!');

        expect($slug->getValue())->toBe('title-with-special-chars');
    });

    it('creates slug from title with multiple spaces', function (): void {
        $slug = Slug::fromTitle('Title   with    multiple    spaces');

        expect($slug->getValue())->toBe('title-with-multiple-spaces');
    });

    it('creates slug from title with leading and trailing spaces', function (): void {
        $slug = Slug::fromTitle('  Title with spaces  ');

        expect($slug->getValue())->toBe('title-with-spaces');
    });

    it('creates slug from title with numbers', function (): void {
        $slug = Slug::fromTitle('Article 123 Version 2');

        expect($slug->getValue())->toBe('article-123-version-2');
    });

    it('checks if slug equals another slug', function (): void {
        $slug1 = Slug::from('same-slug');
        $slug2 = Slug::from('same-slug');
        $slug3 = Slug::from('different-slug');

        expect($slug1->equals($slug2))->toBeTrue()
            ->and($slug1->equals($slug3))->toBeFalse();
    });

    it('throws exception for zero string', function (): void {
        expect(fn (): Slug => Slug::from('0'))
            ->toThrow(InvalidArgumentException::class, SLUG_EMPTY_ERROR);
    });

    it('throws exception for slug too long', function (): void {
        $longSlug = str_repeat('a', 256);

        expect(fn (): Slug => Slug::from($longSlug))
            ->toThrow(InvalidArgumentException::class, 'Slug cannot be longer than 255 characters');
    });

    it('throws exception for slug starting with hyphen', function (): void {
        expect(fn (): Slug => Slug::from('-invalid-slug'))
            ->toThrow(InvalidArgumentException::class, 'Slug cannot start or end with hyphen');
    });

    it('throws exception for slug ending with hyphen', function (): void {
        expect(fn (): Slug => Slug::from('invalid-slug-'))
            ->toThrow(InvalidArgumentException::class, 'Slug cannot start or end with hyphen');
    });

    it('throws exception for consecutive hyphens', function (): void {
        expect(fn (): Slug => Slug::from('invalid--slug'))
            ->toThrow(InvalidArgumentException::class, 'Slug cannot contain consecutive hyphens');
    });

    it('creates slug using constructor', function (): void {
        $slug = new Slug('test-slug');

        expect($slug->getValue())->toBe('test-slug')
            ->and((string) $slug)->toBe('test-slug');
    });

    it('implements Stringable interface', function (): void {
        $slug = Slug::from('test-slug');

        expect($slug)->toBeInstanceOf(\Stringable::class);
    });

    it('creates slug from title with hyphens and special handling', function (): void {
        $slug = Slug::fromTitle('Title - with - hyphens');

        expect($slug->getValue())->toBe('title-with-hyphens');
    });

    it('creates slug from title that results in empty after cleaning', function (): void {
        expect(fn (): Slug => Slug::fromTitle('!@#$%^&*()'))
            ->toThrow(InvalidArgumentException::class, SLUG_EMPTY_ERROR);
    });

    it('handles maximum length slug', function (): void {
        $maxSlug = str_repeat('a', 255);
        $slug = Slug::from($maxSlug);

        expect($slug->getValue())->toBe($maxSlug);
    });

    it('creates slug from complex title with mixed content', function (): void {
        $slug = Slug::fromTitle('  Title!!! With @#$ Mixed 123 Content???  ');

        expect($slug->getValue())->toBe('title-with-mixed-123-content');
    });
});
