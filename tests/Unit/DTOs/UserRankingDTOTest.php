<?php

use App\DTOs\UserRankingDTO;

describe('UserRankingDTO', function (): void {
    it('creates instance with constructor', function (): void {
        $dto = new UserRankingDTO(
            userId: 'user-123',
            rank: 1,
            score: 150.5,
            followersCount: 10,
            articlesCount: 5,
            totalViews: 1000,
            totalLikes: 50,
            totalComments: 25,
            user: ['name' => 'Test User'],
        );

        expect($dto->userId)->toBe('user-123')
            ->and($dto->rank)->toBe(1)
            ->and($dto->score)->toBe(150.5)
            ->and($dto->followersCount)->toBe(10)
            ->and($dto->articlesCount)->toBe(5)
            ->and($dto->totalViews)->toBe(1000)
            ->and($dto->totalLikes)->toBe(50)
            ->and($dto->totalComments)->toBe(25)
            ->and($dto->user)->toBe(['name' => 'Test User']);
    });

    it('creates instance with default values', function (): void {
        $dto = new UserRankingDTO(
            userId: 'user-456',
            rank: null,
            score: 0.0,
        );

        expect($dto->userId)->toBe('user-456')
            ->and($dto->rank)->toBeNull()
            ->and($dto->score)->toBe(0.0)
            ->and($dto->followersCount)->toBe(0)
            ->and($dto->articlesCount)->toBe(0)
            ->and($dto->totalViews)->toBe(0)
            ->and($dto->totalLikes)->toBe(0)
            ->and($dto->totalComments)->toBe(0)
            ->and($dto->user)->toBe([]);
    });

    it('creates from array', function (): void {
        $data = [
            'user_id' => 'user-789',
            'rank' => 5,
            'score' => 250.75,
            'followers_count' => 20,
            'articles_count' => 10,
            'total_views' => 2000,
            'total_likes' => 100,
            'total_comments' => 50,
            'user' => ['name' => 'Array User', 'username' => 'arrayuser'],
        ];

        $dto = UserRankingDTO::fromArray($data);

        expect($dto->userId)->toBe('user-789')
            ->and($dto->rank)->toBe(5)
            ->and($dto->score)->toBe(250.75)
            ->and($dto->followersCount)->toBe(20)
            ->and($dto->articlesCount)->toBe(10)
            ->and($dto->totalViews)->toBe(2000)
            ->and($dto->totalLikes)->toBe(100)
            ->and($dto->totalComments)->toBe(50)
            ->and($dto->user['name'])->toBe('Array User');
    });

    it('creates from empty array with defaults', function (): void {
        $dto = UserRankingDTO::fromArray([]);

        expect($dto->userId)->toBe('')
            ->and($dto->rank)->toBeNull()
            ->and($dto->score)->toBe(0.0)
            ->and($dto->followersCount)->toBe(0)
            ->and($dto->articlesCount)->toBe(0)
            ->and($dto->totalViews)->toBe(0)
            ->and($dto->totalLikes)->toBe(0)
            ->and($dto->totalComments)->toBe(0)
            ->and($dto->user)->toBe([]);
    });

    it('converts to array', function (): void {
        $dto = new UserRankingDTO(
            userId: 'user-array',
            rank: 3,
            score: 175.25,
            followersCount: 15,
            articlesCount: 8,
            totalViews: 1500,
            totalLikes: 75,
            totalComments: 30,
            user: ['name' => 'To Array'],
        );

        $array = $dto->toArray();

        expect($array)->toBe([
            'user_id' => 'user-array',
            'rank' => 3,
            'score' => 175.25,
            'followers_count' => 15,
            'articles_count' => 8,
            'total_views' => 1500,
            'total_likes' => 75,
            'total_comments' => 30,
            'user' => ['name' => 'To Array'],
        ]);
    });

    it('gets influence breakdown', function (): void {
        $dto = new UserRankingDTO(
            userId: 'user-breakdown',
            rank: 1,
            score: 100.0,
            followersCount: 10,
            articlesCount: 5,
            totalViews: 100,
            totalLikes: 20,
            totalComments: 10,
        );

        $breakdown = $dto->getInfluenceBreakdown();

        expect($breakdown)->toHaveKeys(['followers', 'views', 'likes', 'comments', 'articles'])
            ->and($breakdown['followers'])->toBe([
                'value' => 10,
                'weight' => 2.0,
                'contribution' => 20.0,
            ])
            ->and($breakdown['views'])->toBe([
                'value' => 100,
                'weight' => 0.5,
                'contribution' => 50.0,
            ])
            ->and($breakdown['likes'])->toBe([
                'value' => 20,
                'weight' => 1.0,
                'contribution' => 20.0,
            ])
            ->and($breakdown['comments'])->toBe([
                'value' => 10,
                'weight' => 0.8,
                'contribution' => 8.0,
            ])
            ->and($breakdown['articles'])->toBe([
                'value' => 5,
                'weight' => 1.5,
                'contribution' => 7.5,
            ]);
    });

    it('calculates total contribution from breakdown', function (): void {
        $dto = new UserRankingDTO(
            userId: 'user-total',
            rank: 1,
            score: 105.5,
            followersCount: 10,
            articlesCount: 5,
            totalViews: 100,
            totalLikes: 20,
            totalComments: 10,
        );

        $breakdown = $dto->getInfluenceBreakdown();
        $totalContribution = array_sum(array_column($breakdown, 'contribution'));

        expect($totalContribution)->toBe(105.5);
    });
});
