<?php

namespace App\Enums;

/**
 * Recommendation Type Enum.
 *
 * Defines the possible types of recommendations in the system.
 */
enum RecommendationType: string
{
    /** Recommended users based on common followers */
    case Users = 'users';

    /** Recommended articles based on interests */
    case Articles = 'articles';

    /** Recommended authors based on influence */
    case Authors = 'authors';

    /** Recommended topics based on interactions */
    case Topics = 'topics';

    /** Related articles for a specific article */
    case RelatedArticles = 'related_articles';

    /**
     * Get all recommendation type values.
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get type display name.
     *
     * @return string The display label
     */
    public function label(): string
    {
        return match ($this) {
            self::Users => 'Recommended Users',
            self::Articles => 'Recommended Articles',
            self::Authors => 'Suggested Authors',
            self::Topics => 'Topics of Interest',
            self::RelatedArticles => 'Related Articles',
        };
    }

    /**
     * Get description for the recommendation type.
     *
     * @return string The description
     */
    public function description(): string
    {
        return match ($this) {
            self::Users => 'Users with common followers',
            self::Articles => 'Articles based on your interests',
            self::Authors => 'Influential authors on the platform',
            self::Topics => 'Topics based on your interactions',
            self::RelatedArticles => 'Similar articles by tags and categories',
        };
    }
}
