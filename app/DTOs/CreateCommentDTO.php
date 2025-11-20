<?php

namespace App\DTOs;

use Illuminate\Support\Arr;

/**
 * Data Transfer Object for creating a comment.
 *
 * Encapsulates the data required to create a new comment.
 */
final readonly class CreateCommentDTO
{
    public function __construct(
        public string $articleId,
        public string $userId,
        public string $content,
    ) {}

    /**
     * Create a DTO from an array.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $articleId = Arr::get($data, 'article_id', '');
        $userId = Arr::get($data, 'user_id', '');
        $content = Arr::get($data, 'content', '');

        return new self(
            articleId: is_string($articleId) ? $articleId : '',
            userId: is_string($userId) ? $userId : '',
            content: is_string($content) ? $content : '',
        );
    }

    /**
     * Convert the DTO to an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'article_id' => $this->articleId,
            'user_id' => $this->userId,
            'content' => $this->content,
        ];
    }
}
