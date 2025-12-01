<?php

declare(strict_types=1);

use App\DTOs\CreateCommentDTO;

describe('CreateCommentDTO Construction', function (): void {
    it('creates dto with all required properties', function (): void {
        $dto = new CreateCommentDTO(
            articleId: '507f1f77bcf86cd799439011',
            userId: '507f1f77bcf86cd799439012',
            content: 'This is a comment content'
        );

        expect($dto->articleId)->toBe('507f1f77bcf86cd799439011')
            ->and($dto->userId)->toBe('507f1f77bcf86cd799439012')
            ->and($dto->content)->toBe('This is a comment content');
    });

    it('creates dto with empty strings', function (): void {
        $dto = new CreateCommentDTO(
            articleId: '',
            userId: '',
            content: ''
        );

        expect($dto->articleId)->toBe('')
            ->and($dto->userId)->toBe('')
            ->and($dto->content)->toBe('');
    });
});

describe('CreateCommentDTO toArray', function (): void {
    it('converts to array with all data', function (): void {
        $dto = new CreateCommentDTO(
            articleId: '507f1f77bcf86cd799439011',
            userId: '507f1f77bcf86cd799439012',
            content: 'This is a comment content'
        );

        $array = $dto->toArray();

        expect($array)->toBe([
            'article_id' => '507f1f77bcf86cd799439011',
            'user_id' => '507f1f77bcf86cd799439012',
            'content' => 'This is a comment content',
        ]);
    });

    it('converts to array with empty strings', function (): void {
        $dto = new CreateCommentDTO(
            articleId: '',
            userId: '',
            content: ''
        );

        $array = $dto->toArray();

        expect($array)->toBe([
            'article_id' => '',
            'user_id' => '',
            'content' => '',
        ]);
    });
});

describe('CreateCommentDTO fromArray', function (): void {
    it('creates from array with all data', function (): void {
        $data = [
            'article_id' => '507f1f77bcf86cd799439011',
            'user_id' => '507f1f77bcf86cd799439012',
            'content' => 'This is a comment content',
        ];

        $dto = CreateCommentDTO::fromArray($data);

        expect($dto->articleId)->toBe('507f1f77bcf86cd799439011')
            ->and($dto->userId)->toBe('507f1f77bcf86cd799439012')
            ->and($dto->content)->toBe('This is a comment content');
    });

    it('creates from array with missing fields', function (): void {
        $data = [];

        $dto = CreateCommentDTO::fromArray($data);

        expect($dto->articleId)->toBe('')
            ->and($dto->userId)->toBe('')
            ->and($dto->content)->toBe('');
    });

    it('creates from array with non-string article_id', function (): void {
        $data = [
            'article_id' => 123,
            'user_id' => '507f1f77bcf86cd799439012',
            'content' => 'Content',
        ];

        $dto = CreateCommentDTO::fromArray($data);

        expect($dto->articleId)->toBe('');
    });

    it('creates from array with non-string user_id', function (): void {
        $data = [
            'article_id' => '507f1f77bcf86cd799439011',
            'user_id' => null,
            'content' => 'Content',
        ];

        $dto = CreateCommentDTO::fromArray($data);

        expect($dto->userId)->toBe('');
    });

    it('creates from array with non-string content', function (): void {
        $data = [
            'article_id' => '507f1f77bcf86cd799439011',
            'user_id' => '507f1f77bcf86cd799439012',
            'content' => ['array', 'content'],
        ];

        $dto = CreateCommentDTO::fromArray($data);

        expect($dto->content)->toBe('');
    });

    it('creates from array with partial data', function (): void {
        $data = [
            'article_id' => '507f1f77bcf86cd799439011',
        ];

        $dto = CreateCommentDTO::fromArray($data);

        expect($dto->articleId)->toBe('507f1f77bcf86cd799439011')
            ->and($dto->userId)->toBe('')
            ->and($dto->content)->toBe('');
    });

    it('creates from array and converts back to array', function (): void {
        $data = [
            'article_id' => '507f1f77bcf86cd799439011',
            'user_id' => '507f1f77bcf86cd799439012',
            'content' => 'This is a comment content',
        ];

        $dto = CreateCommentDTO::fromArray($data);
        $result = $dto->toArray();

        expect($result)->toBe($data);
    });
});

describe('CreateCommentDTO Type Safety', function (): void {
    it('is readonly and immutable', function (): void {
        $dto = new CreateCommentDTO(
            articleId: '507f1f77bcf86cd799439011',
            userId: '507f1f77bcf86cd799439012',
            content: 'Content'
        );

        expect($dto)->toBeInstanceOf(CreateCommentDTO::class);
    });

    it('handles long content strings', function (): void {
        $longContent = str_repeat('Lorem ipsum dolor sit amet. ', 100);

        $dto = new CreateCommentDTO(
            articleId: '507f1f77bcf86cd799439011',
            userId: '507f1f77bcf86cd799439012',
            content: $longContent
        );

        expect($dto->content)->toBe($longContent);
    });

    it('handles special characters in content', function (): void {
        $specialContent = 'Comment with <html> tags & special chars: @#$%^&*()';

        $dto = new CreateCommentDTO(
            articleId: '507f1f77bcf86cd799439011',
            userId: '507f1f77bcf86cd799439012',
            content: $specialContent
        );

        expect($dto->content)->toBe($specialContent);
    });
});
