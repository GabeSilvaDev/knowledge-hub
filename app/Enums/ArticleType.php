<?php

namespace App\Enums;

/**
 * Article Type Enum.
 *
 * Defines the different content types available for articles.
 */
enum ArticleType: string
{
    /** Standard article format */
    case ARTICLE = 'article';

    /** Blog post format */
    case POST = 'post';

    /** Wiki/documentation format */
    case WIKI = 'wiki';

    /** Tutorial/how-to format */
    case TUTORIAL = 'tutorial';

    /** News/announcement format */
    case NEWS = 'news';

    /**
     * Get all type values.
     *
     * Returns an array of all available type string values.
     *
     * @return array<string> Array of type values
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get type display name.
     *
     * Returns a localized, human-readable label for the type.
     *
     * @return string The display label in Portuguese
     */
    public function label(): string
    {
        return match ($this) {
            self::ARTICLE => 'Artigo',
            self::POST => 'Post',
            self::WIKI => 'Wiki',
            self::TUTORIAL => 'Tutorial',
            self::NEWS => 'Notícia',
        };
    }

    /**
     * Get type icon.
     *
     * Returns an emoji icon representing the article type.
     *
     * @return string Unicode emoji character
     */
    public function icon(): string
    {
        return match ($this) {
            self::ARTICLE => '📝',
            self::POST => '💬',
            self::WIKI => '📚',
            self::TUTORIAL => '🎓',
            self::NEWS => '📰',
        };
    }
}
