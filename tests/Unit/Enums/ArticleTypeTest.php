<?php

use App\Enums\ArticleType;

describe('ArticleType Enum', function (): void {
    it('has all expected type cases', function (): void {
        $cases = ArticleType::cases();

        expect($cases)->toHaveCount(5)
            ->and($cases[0])->toBe(ArticleType::ARTICLE)
            ->and($cases[1])->toBe(ArticleType::POST)
            ->and($cases[2])->toBe(ArticleType::WIKI)
            ->and($cases[3])->toBe(ArticleType::TUTORIAL)
            ->and($cases[4])->toBe(ArticleType::NEWS);
    });

    it('has correct string values', function (): void {
        expect(ArticleType::ARTICLE->value)->toBe('article')
            ->and(ArticleType::POST->value)->toBe('post')
            ->and(ArticleType::WIKI->value)->toBe('wiki')
            ->and(ArticleType::TUTORIAL->value)->toBe('tutorial')
            ->and(ArticleType::NEWS->value)->toBe('news');
    });

    it('returns all values', function (): void {
        $values = ArticleType::values();

        expect($values)->toBe(['article', 'post', 'wiki', 'tutorial', 'news'])
            ->and($values)->toHaveCount(5);
    });

    it('can be created from string value', function (): void {
        expect(ArticleType::from('article'))->toBe(ArticleType::ARTICLE)
            ->and(ArticleType::from('post'))->toBe(ArticleType::POST)
            ->and(ArticleType::from('wiki'))->toBe(ArticleType::WIKI)
            ->and(ArticleType::from('tutorial'))->toBe(ArticleType::TUTORIAL)
            ->and(ArticleType::from('news'))->toBe(ArticleType::NEWS);
    });

    it('throws exception for invalid value', function (): void {
        expect(fn () => ArticleType::from('invalid'))
            ->toThrow(ValueError::class);
    });

    it('returns correct labels', function (): void {
        expect(ArticleType::ARTICLE->label())->toBe('Artigo')
            ->and(ArticleType::POST->label())->toBe('Post')
            ->and(ArticleType::WIKI->label())->toBe('Wiki')
            ->and(ArticleType::TUTORIAL->label())->toBe('Tutorial')
            ->and(ArticleType::NEWS->label())->toBe('Notícia');
    });

    it('returns correct icons', function (): void {
        expect(ArticleType::ARTICLE->icon())->toBe('📝')
            ->and(ArticleType::POST->icon())->toBe('💬')
            ->and(ArticleType::WIKI->icon())->toBe('📚')
            ->and(ArticleType::TUTORIAL->icon())->toBe('🎓')
            ->and(ArticleType::NEWS->icon())->toBe('📰');
    });

    it('can be used in match expressions', function (): void {
        $getDescription = (fn (ArticleType $type): string => match ($type) {
            ArticleType::ARTICLE => 'Long-form content',
            ArticleType::POST => 'Short-form content',
            ArticleType::WIKI => 'Documentation content',
            ArticleType::TUTORIAL => 'Educational content',
            ArticleType::NEWS => 'News content',
        });

        expect($getDescription(ArticleType::ARTICLE))->toBe('Long-form content')
            ->and($getDescription(ArticleType::POST))->toBe('Short-form content')
            ->and($getDescription(ArticleType::WIKI))->toBe('Documentation content')
            ->and($getDescription(ArticleType::TUTORIAL))->toBe('Educational content')
            ->and($getDescription(ArticleType::NEWS))->toBe('News content');
    });

    it('can be compared for equality', function (): void {
        $article1 = ArticleType::ARTICLE;
        $article2 = ArticleType::ARTICLE;

        expect($article1 === $article2)->toBeTrue()
            ->and(ArticleType::ARTICLE === ArticleType::POST)->toBeFalse()
            ->and(ArticleType::TUTORIAL !== ArticleType::WIKI)->toBeTrue();
    });

    it('can use tryFrom for safe creation', function (): void {
        expect(ArticleType::tryFrom('article'))->toBe(ArticleType::ARTICLE)
            ->and(ArticleType::tryFrom('tutorial'))->toBe(ArticleType::TUTORIAL)
            ->and(ArticleType::tryFrom('invalid'))->toBeNull()
            ->and(ArticleType::tryFrom(''))->toBeNull();
    });

    it('works correctly in arrays and collections', function (): void {
        $types = [ArticleType::ARTICLE, ArticleType::TUTORIAL];

        expect(in_array(ArticleType::ARTICLE, $types))->toBeTrue()
            ->and(in_array(ArticleType::POST, $types))->toBeFalse()
            ->and(count($types))->toBe(2);
    });

    it('can be serialized to string', function (): void {
        expect((string) ArticleType::ARTICLE->value)->toBe('article')
            ->and((string) ArticleType::TUTORIAL->value)->toBe('tutorial')
            ->and((string) ArticleType::NEWS->value)->toBe('news');
    });

    it('supports filtering by specific types', function (): void {
        $allTypes = ArticleType::cases();
        $contentTypes = array_filter($allTypes, fn (ArticleType $type): bool => in_array($type, [ArticleType::ARTICLE, ArticleType::POST]));
        $educationalTypes = array_filter($allTypes, fn (ArticleType $type): bool => in_array($type, [ArticleType::TUTORIAL, ArticleType::WIKI]));

        expect($contentTypes)->toHaveCount(2)
            ->and($educationalTypes)->toHaveCount(2)
            ->and(in_array(ArticleType::ARTICLE, $contentTypes))->toBeTrue()
            ->and(in_array(ArticleType::TUTORIAL, $educationalTypes))->toBeTrue();
    });
});
