<?php

namespace App\Enums;

/**
 * Article Status Enum.
 *
 * Defines the possible states an article can be in throughout its lifecycle.
 */
enum ArticleStatus: string
{
    /** Draft state - article is being written */
    case DRAFT = 'draft';

    /** Published state - article is publicly visible */
    case PUBLISHED = 'published';

    /** Private state - article is only visible to author */
    case PRIVATE = 'private';

    /** Archived state - article is no longer active */
    case ARCHIVED = 'archived';

    /**
     * Get all status values.
     *
     * Returns an array of all available status string values.
     *
     * @return array<string> Array of status values
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get status display name.
     *
     * Returns a localized, human-readable label for the status.
     *
     * @return string The display label in Portuguese
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
     *
     * Determines if articles with this status should be publicly accessible.
     *
     * @return bool True if status allows public visibility
     */
    public function isPublic(): bool
    {
        return $this === self::PUBLISHED;
    }

    /**
     * Check if status allows editing.
     *
     * Determines if articles with this status can be modified.
     *
     * @return bool True if status allows editing
     */
    public function isEditable(): bool
    {
        return in_array($this, [self::DRAFT, self::PRIVATE]);
    }
}
