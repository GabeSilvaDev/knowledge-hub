<?php

namespace App\DTOs;

use App\Enums\RecommendationType;
use Illuminate\Support\Collection;

/**
 * Data Transfer Object for Recommendations.
 *
 * Encapsulates recommendation data from Neo4j graph queries.
 */
class RecommendationDTO
{
    /**
     * Create a new Recommendation DTO instance.
     *
     * @param  RecommendationType  $type  The type of recommendation
     * @param  Collection<int, array<string, mixed>>  $items  Collection of recommended items
     * @param  int  $totalCount  Total count of available recommendations
     * @param  string|null  $forUserId  User ID for whom recommendations are made
     * @param  string|null  $forArticleId  Article ID for related article recommendations
     * @param  array<string, mixed>  $metadata  Additional metadata about the recommendations
     */
    public function __construct(
        public readonly RecommendationType $type,
        public readonly Collection $items,
        public readonly int $totalCount,
        public readonly ?string $forUserId = null,
        public readonly ?string $forArticleId = null,
        /** @var array<string, mixed> */
        public readonly array $metadata = [],
    ) {}

    /**
     * Create DTO from array.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        /** @var array<int, array<string, mixed>> $items */
        $items = $data['items'] ?? [];

        /** @var string $type */
        $type = $data['type'] ?? 'users';

        /** @var int $totalCount */
        $totalCount = $data['total_count'] ?? 0;

        /** @var string|null $forUserId */
        $forUserId = $data['for_user_id'] ?? null;

        /** @var string|null $forArticleId */
        $forArticleId = $data['for_article_id'] ?? null;

        /** @var array<string, mixed> $metadata */
        $metadata = $data['metadata'] ?? [];

        return new self(
            type: RecommendationType::from($type),
            items: collect($items),
            totalCount: (int) $totalCount,
            forUserId: $forUserId !== null ? (string) $forUserId : null,
            forArticleId: $forArticleId !== null ? (string) $forArticleId : null,
            metadata: $metadata,
        );
    }

    /**
     * Convert DTO to array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type->value,
            'items' => $this->items->toArray(),
            'total_count' => $this->totalCount,
            'for_user_id' => $this->forUserId,
            'for_article_id' => $this->forArticleId,
            'metadata' => $this->metadata,
        ];
    }

    /**
     * Check if recommendations are empty.
     */
    public function isEmpty(): bool
    {
        return $this->items->isEmpty();
    }

    /**
     * Get count of items.
     */
    public function count(): int
    {
        return $this->items->count();
    }

    /**
     * Create empty recommendations DTO.
     */
    public static function empty(RecommendationType $type, ?string $forUserId = null, ?string $forArticleId = null): self
    {
        /** @var Collection<int, array<string, mixed>> $emptyCollection */
        $emptyCollection = collect();

        return new self(
            type: $type,
            items: $emptyCollection,
            totalCount: 0,
            forUserId: $forUserId,
            forArticleId: $forArticleId,
            metadata: ['empty' => true],
        );
    }
}
