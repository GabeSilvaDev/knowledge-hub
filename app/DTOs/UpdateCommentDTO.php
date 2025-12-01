<?php

namespace App\DTOs;

use Illuminate\Support\Arr;

/**
 * Data Transfer Object for updating a comment.
 *
 * Encapsulates the data required to update an existing comment.
 */
final readonly class UpdateCommentDTO
{
    public function __construct(
        public string $content,
    ) {}

    /**
     * Create a DTO from an array.
     *
     * @param  array<string, mixed>  $data  The input data array
     * @return self The created DTO instance
     */
    public static function fromArray(array $data): self
    {
        $content = Arr::get($data, 'content', '');

        return new self(
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
            'content' => $this->content,
        ];
    }
}
