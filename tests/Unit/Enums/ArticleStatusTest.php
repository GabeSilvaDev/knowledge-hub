<?php

use App\Enums\ArticleStatus;

describe('ArticleStatus Enum', function (): void {
    it('has all expected status cases', function (): void {
        $cases = ArticleStatus::cases();

        expect($cases)->toHaveCount(4)
            ->and($cases[0])->toBe(ArticleStatus::DRAFT)
            ->and($cases[1])->toBe(ArticleStatus::PUBLISHED)
            ->and($cases[2])->toBe(ArticleStatus::PRIVATE)
            ->and($cases[3])->toBe(ArticleStatus::ARCHIVED);
    });

    it('has correct string values', function (): void {
        expect(ArticleStatus::DRAFT->value)->toBe('draft')
            ->and(ArticleStatus::PUBLISHED->value)->toBe('published')
            ->and(ArticleStatus::PRIVATE->value)->toBe('private')
            ->and(ArticleStatus::ARCHIVED->value)->toBe('archived');
    });

    it('returns all values', function (): void {
        $values = ArticleStatus::values();

        expect($values)->toBe(['draft', 'published', 'private', 'archived'])
            ->and($values)->toHaveCount(4);
    });

    it('can be created from string value', function (): void {
        expect(ArticleStatus::from('draft'))->toBe(ArticleStatus::DRAFT)
            ->and(ArticleStatus::from('published'))->toBe(ArticleStatus::PUBLISHED)
            ->and(ArticleStatus::from('private'))->toBe(ArticleStatus::PRIVATE)
            ->and(ArticleStatus::from('archived'))->toBe(ArticleStatus::ARCHIVED);
    });

    it('throws exception for invalid value', function (): void {
        expect(fn () => ArticleStatus::from('invalid'))
            ->toThrow(ValueError::class);
    });

    it('returns correct labels', function (): void {
        expect(ArticleStatus::DRAFT->label())->toBe('Rascunho')
            ->and(ArticleStatus::PUBLISHED->label())->toBe('Publicado')
            ->and(ArticleStatus::PRIVATE->label())->toBe('Privado')
            ->and(ArticleStatus::ARCHIVED->label())->toBe('Arquivado');
    });

    it('identifies public status correctly', function (): void {
        expect(ArticleStatus::PUBLISHED->isPublic())->toBeTrue()
            ->and(ArticleStatus::DRAFT->isPublic())->toBeFalse()
            ->and(ArticleStatus::PRIVATE->isPublic())->toBeFalse()
            ->and(ArticleStatus::ARCHIVED->isPublic())->toBeFalse();
    });

    it('identifies editable status correctly', function (): void {
        expect(ArticleStatus::DRAFT->isEditable())->toBeTrue()
            ->and(ArticleStatus::PRIVATE->isEditable())->toBeTrue()
            ->and(ArticleStatus::PUBLISHED->isEditable())->toBeFalse()
            ->and(ArticleStatus::ARCHIVED->isEditable())->toBeFalse();
    });

    it('can be used in match expressions', function (): void {
        $getDescription = (fn (ArticleStatus $status): string => match ($status) {
            ArticleStatus::DRAFT => 'Article in draft state',
            ArticleStatus::PUBLISHED => 'Article is live',
            ArticleStatus::PRIVATE => 'Article is private',
            ArticleStatus::ARCHIVED => 'Article is archived',
        });

        expect($getDescription(ArticleStatus::DRAFT))->toBe('Article in draft state')
            ->and($getDescription(ArticleStatus::PUBLISHED))->toBe('Article is live')
            ->and($getDescription(ArticleStatus::PRIVATE))->toBe('Article is private')
            ->and($getDescription(ArticleStatus::ARCHIVED))->toBe('Article is archived');
    });

    it('can be compared for equality', function (): void {
        $draft1 = ArticleStatus::DRAFT;
        $draft2 = ArticleStatus::DRAFT;

        expect($draft1 === $draft2)->toBeTrue()
            ->and(ArticleStatus::DRAFT === ArticleStatus::PUBLISHED)->toBeFalse()
            ->and(ArticleStatus::PUBLISHED !== ArticleStatus::PRIVATE)->toBeTrue();
    });

    it('can use tryFrom for safe creation', function (): void {
        expect(ArticleStatus::tryFrom('draft'))->toBe(ArticleStatus::DRAFT)
            ->and(ArticleStatus::tryFrom('published'))->toBe(ArticleStatus::PUBLISHED)
            ->and(ArticleStatus::tryFrom('invalid'))->toBeNull()
            ->and(ArticleStatus::tryFrom(''))->toBeNull();
    });

    it('works correctly in arrays and collections', function (): void {
        $statuses = [ArticleStatus::DRAFT, ArticleStatus::PUBLISHED];

        expect(in_array(ArticleStatus::DRAFT, $statuses))->toBeTrue()
            ->and(in_array(ArticleStatus::PRIVATE, $statuses))->toBeFalse()
            ->and(count($statuses))->toBe(2);
    });
});
