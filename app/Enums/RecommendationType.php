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
     * @return string The display label in Portuguese
     */
    public function label(): string
    {
        return match ($this) {
            self::Users => 'Usuários Recomendados',
            self::Articles => 'Artigos Recomendados',
            self::Authors => 'Autores Sugeridos',
            self::Topics => 'Tópicos de Interesse',
            self::RelatedArticles => 'Artigos Relacionados',
        };
    }

    /**
     * Get description for the recommendation type.
     *
     * @return string The description in Portuguese
     */
    public function description(): string
    {
        return match ($this) {
            self::Users => 'Usuários com seguidores em comum',
            self::Articles => 'Artigos baseados em seus interesses',
            self::Authors => 'Autores influentes na plataforma',
            self::Topics => 'Tópicos baseados em suas interações',
            self::RelatedArticles => 'Artigos similares por tags e categorias',
        };
    }
}
