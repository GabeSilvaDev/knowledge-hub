<?php

use App\Enums\RecommendationType;

describe('RecommendationType Enum', function (): void {
    describe('values', function (): void {
        it('has users type', function (): void {
            expect(RecommendationType::Users->value)->toBe('users');
        });

        it('has articles type', function (): void {
            expect(RecommendationType::Articles->value)->toBe('articles');
        });

        it('has authors type', function (): void {
            expect(RecommendationType::Authors->value)->toBe('authors');
        });

        it('has topics type', function (): void {
            expect(RecommendationType::Topics->value)->toBe('topics');
        });

        it('has related_articles type', function (): void {
            expect(RecommendationType::RelatedArticles->value)->toBe('related_articles');
        });
    });

    describe('values method', function (): void {
        it('returns all type values', function (): void {
            $values = RecommendationType::values();

            expect($values)->toBeArray()
                ->and($values)->toContain('users', 'articles', 'authors', 'topics', 'related_articles')
                ->and($values)->toHaveCount(5);
        });
    });

    describe('label method', function (): void {
        it('returns correct label for users', function (): void {
            expect(RecommendationType::Users->label())->toBe('Recommended Users');
        });

        it('returns correct label for articles', function (): void {
            expect(RecommendationType::Articles->label())->toBe('Recommended Articles');
        });

        it('returns correct label for authors', function (): void {
            expect(RecommendationType::Authors->label())->toBe('Suggested Authors');
        });

        it('returns correct label for topics', function (): void {
            expect(RecommendationType::Topics->label())->toBe('Topics of Interest');
        });

        it('returns correct label for related articles', function (): void {
            expect(RecommendationType::RelatedArticles->label())->toBe('Related Articles');
        });
    });

    describe('description method', function (): void {
        it('returns correct description for users', function (): void {
            expect(RecommendationType::Users->description())
                ->toBe('Users with common followers');
        });

        it('returns correct description for articles', function (): void {
            expect(RecommendationType::Articles->description())
                ->toBe('Articles based on your interests');
        });

        it('returns correct description for authors', function (): void {
            expect(RecommendationType::Authors->description())
                ->toBe('Influential authors on the platform');
        });

        it('returns correct description for topics', function (): void {
            expect(RecommendationType::Topics->description())
                ->toBe('Topics based on your interactions');
        });

        it('returns correct description for related articles', function (): void {
            expect(RecommendationType::RelatedArticles->description())
                ->toBe('Similar articles by tags and categories');
        });
    });

    describe('from method', function (): void {
        it('creates enum from string value', function (): void {
            expect(RecommendationType::from('users'))->toBe(RecommendationType::Users);
            expect(RecommendationType::from('articles'))->toBe(RecommendationType::Articles);
            expect(RecommendationType::from('authors'))->toBe(RecommendationType::Authors);
            expect(RecommendationType::from('topics'))->toBe(RecommendationType::Topics);
            expect(RecommendationType::from('related_articles'))->toBe(RecommendationType::RelatedArticles);
        });

        it('throws exception for invalid value', function (): void {
            RecommendationType::from('invalid');
        })->throws(ValueError::class);
    });

    describe('tryFrom method', function (): void {
        it('returns enum for valid value', function (): void {
            expect(RecommendationType::tryFrom('users'))->toBe(RecommendationType::Users);
        });

        it('returns null for invalid value', function (): void {
            expect(RecommendationType::tryFrom('invalid'))->toBeNull();
        });
    });
});
