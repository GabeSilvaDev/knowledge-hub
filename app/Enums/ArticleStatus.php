<?php

namespace App\Enums;

enum ArticleStatus: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case PRIVATE = 'private';
    case ARCHIVED = 'archived';

    /**
     * Get all status values.
     */
    /**
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get status display name.
     */
    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Rascunho',
            self::PUBLISHED => 'Publicado',
            self::PRIVATE => 'Privado',
            self::ARCHIVED => 'Arquivado',
        };
    }

    /**
     * Check if status is visible to public.
     */
    public function isPublic(): bool
    {
        return $this === self::PUBLISHED;
    }

    /**
     * Check if status allows editing.
     */
    public function isEditable(): bool
    {
        return in_array($this, [self::DRAFT, self::PRIVATE]);
    }
}
