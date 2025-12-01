<?php

use App\DTOs\RecommendationDTO;
use App\Enums\RecommendationType;

describe('RecommendationDTO', function (): void {
    describe('constructor and properties', function (): void {
        it('creates DTO with all properties', function (): void {
            $items = collect([
                ['id' => '1', 'name' => 'Item 1'],
                ['id' => '2', 'name' => 'Item 2'],
            ]);

            $dto = new RecommendationDTO(
                type: RecommendationType::Users,
                items: $items,
                totalCount: 2,
                forUserId: 'user-123',
                forArticleId: null,
                metadata: ['algorithm' => 'test'],
            );

            expect($dto->type)->toBe(RecommendationType::Users)
                ->and($dto->items->count())->toBe(2)
                ->and($dto->totalCount)->toBe(2)
                ->and($dto->forUserId)->toBe('user-123')
                ->and($dto->forArticleId)->toBeNull()
                ->and($dto->metadata)->toBe(['algorithm' => 'test']);
        });

        it('creates DTO with article ID', function (): void {
            $dto = new RecommendationDTO(
                type: RecommendationType::RelatedArticles,
                items: collect(),
                totalCount: 0,
                forArticleId: 'article-123',
            );

            expect($dto->forArticleId)->toBe('article-123')
                ->and($dto->forUserId)->toBeNull();
        });
    });

    describe('fromArray', function (): void {
        it('creates DTO from array', function (): void {
            $data = [
                'type' => 'users',
                'items' => [
                    ['id' => '1', 'name' => 'User 1'],
                ],
                'total_count' => 1,
                'for_user_id' => 'user-123',
                'metadata' => ['test' => true],
            ];

            $dto = RecommendationDTO::fromArray($data);

            expect($dto->type)->toBe(RecommendationType::Users)
                ->and($dto->items->count())->toBe(1)
                ->and($dto->totalCount)->toBe(1)
                ->and($dto->forUserId)->toBe('user-123');
        });

        it('handles missing optional fields', function (): void {
            $data = [
                'type' => 'articles',
                'items' => [],
                'total_count' => 0,
            ];

            $dto = RecommendationDTO::fromArray($data);

            expect($dto->forUserId)->toBeNull()
                ->and($dto->forArticleId)->toBeNull()
                ->and($dto->metadata)->toBe([]);
        });
    });

    describe('toArray', function (): void {
        it('converts DTO to array', function (): void {
            $items = collect([
                ['id' => '1', 'name' => 'Item 1'],
            ]);

            $dto = new RecommendationDTO(
                type: RecommendationType::Authors,
                items: $items,
                totalCount: 1,
                forUserId: 'user-123',
                metadata: ['algorithm' => 'followers'],
            );

            $array = $dto->toArray();

            expect($array)->toHaveKeys(['type', 'items', 'total_count', 'for_user_id', 'for_article_id', 'metadata'])
                ->and($array['type'])->toBe('authors')
                ->and($array['total_count'])->toBe(1)
                ->and($array['for_user_id'])->toBe('user-123');
        });
    });

    describe('isEmpty', function (): void {
        it('returns true when items are empty', function (): void {
            $dto = new RecommendationDTO(
                type: RecommendationType::Topics,
                items: collect(),
                totalCount: 0,
            );

            expect($dto->isEmpty())->toBeTrue();
        });

        it('returns false when items exist', function (): void {
            $dto = new RecommendationDTO(
                type: RecommendationType::Topics,
                items: collect([['name' => 'test']]),
                totalCount: 1,
            );

            expect($dto->isEmpty())->toBeFalse();
        });
    });

    describe('count', function (): void {
        it('returns the number of items', function (): void {
            $dto = new RecommendationDTO(
                type: RecommendationType::Users,
                items: collect([
                    ['id' => '1'],
                    ['id' => '2'],
                    ['id' => '3'],
                ]),
                totalCount: 3,
            );

            expect($dto->count())->toBe(3);
        });
    });

    describe('empty factory method', function (): void {
        it('creates empty DTO with correct type', function (): void {
            $dto = RecommendationDTO::empty(RecommendationType::Users, 'user-123');

            expect($dto->type)->toBe(RecommendationType::Users)
                ->and($dto->isEmpty())->toBeTrue()
                ->and($dto->totalCount)->toBe(0)
                ->and($dto->forUserId)->toBe('user-123')
                ->and($dto->metadata)->toHaveKey('empty', true);
        });

        it('creates empty DTO for articles', function (): void {
            $dto = RecommendationDTO::empty(RecommendationType::RelatedArticles, null, 'article-123');

            expect($dto->type)->toBe(RecommendationType::RelatedArticles)
                ->and($dto->forArticleId)->toBe('article-123');
        });
    });
});
