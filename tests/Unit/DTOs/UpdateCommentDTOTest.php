<?php

declare(strict_types=1);

use App\DTOs\UpdateCommentDTO;

describe('UpdateCommentDTO Construction', function (): void {
    it('creates dto with content', function (): void {
        $dto = new UpdateCommentDTO(
            content: 'Updated comment content'
        );

        expect($dto->content)->toBe('Updated comment content');
    });

    it('creates dto with empty content', function (): void {
        $dto = new UpdateCommentDTO(
            content: ''
        );

        expect($dto->content)->toBe('');
    });

    it('creates dto with long content', function (): void {
        $longContent = str_repeat('This is a very long comment. ', 50);

        $dto = new UpdateCommentDTO(
            content: $longContent
        );

        expect($dto->content)->toBe($longContent);
    });

    it('creates dto with special characters', function (): void {
        $specialContent = 'Content with <b>HTML</b> & special chars: @#$%^&*()';

        $dto = new UpdateCommentDTO(
            content: $specialContent
        );

        expect($dto->content)->toBe($specialContent);
    });
});

describe('UpdateCommentDTO toArray', function (): void {
    it('converts to array with content', function (): void {
        $dto = new UpdateCommentDTO(
            content: 'Updated comment content'
        );

        $array = $dto->toArray();

        expect($array)->toBe([
            'content' => 'Updated comment content',
        ]);
    });

    it('converts to array with empty content', function (): void {
        $dto = new UpdateCommentDTO(
            content: ''
        );

        $array = $dto->toArray();

        expect($array)->toBe([
            'content' => '',
        ]);
    });

    it('converts to array with multiline content', function (): void {
        $multilineContent = "Line 1\nLine 2\nLine 3";

        $dto = new UpdateCommentDTO(
            content: $multilineContent
        );

        $array = $dto->toArray();

        expect($array)->toBe([
            'content' => $multilineContent,
        ]);
    });
});

describe('UpdateCommentDTO fromArray', function (): void {
    it('creates from array with content', function (): void {
        $data = [
            'content' => 'Updated comment content',
        ];

        $dto = UpdateCommentDTO::fromArray($data);

        expect($dto->content)->toBe('Updated comment content');
    });

    it('creates from array with missing content field', function (): void {
        $data = [];

        $dto = UpdateCommentDTO::fromArray($data);

        expect($dto->content)->toBe('');
    });

    it('creates from array with null content', function (): void {
        $data = [
            'content' => null,
        ];

        $dto = UpdateCommentDTO::fromArray($data);

        expect($dto->content)->toBe('');
    });

    it('creates from array with non-string content (array)', function (): void {
        $data = [
            'content' => ['not', 'a', 'string'],
        ];

        $dto = UpdateCommentDTO::fromArray($data);

        expect($dto->content)->toBe('');
    });

    it('creates from array with non-string content (integer)', function (): void {
        $data = [
            'content' => 12345,
        ];

        $dto = UpdateCommentDTO::fromArray($data);

        expect($dto->content)->toBe('');
    });

    it('creates from array with non-string content (boolean)', function (): void {
        $data = [
            'content' => true,
        ];

        $dto = UpdateCommentDTO::fromArray($data);

        expect($dto->content)->toBe('');
    });

    it('creates from array and converts back to array', function (): void {
        $data = [
            'content' => 'Updated comment content',
        ];

        $dto = UpdateCommentDTO::fromArray($data);
        $result = $dto->toArray();

        expect($result)->toBe($data);
    });

    it('creates from array with extra fields', function (): void {
        $data = [
            'content' => 'Valid content',
            'extra_field' => 'Should be ignored',
            'another_field' => 123,
        ];

        $dto = UpdateCommentDTO::fromArray($data);

        expect($dto->content)->toBe('Valid content')
            ->and($dto->toArray())->toBe([
                'content' => 'Valid content',
            ]);
    });
});

describe('UpdateCommentDTO Type Safety', function (): void {
    it('is readonly and immutable', function (): void {
        $dto = new UpdateCommentDTO(
            content: 'Content'
        );

        expect($dto)->toBeInstanceOf(UpdateCommentDTO::class);
    });

    it('handles unicode content', function (): void {
        $unicodeContent = 'Comment with émojis 🎉 and ñ special chârs';

        $dto = new UpdateCommentDTO(
            content: $unicodeContent
        );

        expect($dto->content)->toBe($unicodeContent);
    });

    it('handles whitespace variations', function (): void {
        $whitespaceContent = "  Content with   multiple    spaces  \n\t and tabs  ";

        $dto = new UpdateCommentDTO(
            content: $whitespaceContent
        );

        expect($dto->content)->toBe($whitespaceContent);
    });

    it('preserves content exactly as provided', function (): void {
        $exactContent = '<script>alert("XSS")</script>';

        $dto = new UpdateCommentDTO(
            content: $exactContent
        );

        expect($dto->content)->toBe($exactContent);
    });
});
