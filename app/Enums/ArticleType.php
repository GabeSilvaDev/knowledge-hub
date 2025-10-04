<?php

namespace App\Enums;

enum ArticleType: string
{
    case ARTICLE = 'article';
    case POST = 'post';
    case WIKI = 'wiki';
    case TUTORIAL = 'tutorial';
    case NEWS = 'news';

    /**
     * Get all type values.
     */
    /**
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get type display name.
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
