<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case AUTHOR = 'author';
    case MODERATOR = 'moderator';
    case READER = 'reader';

    /**
     * Get all role values.
     */
    /**
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get role display name.
     */
    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Administrador',
            self::AUTHOR => 'Autor',
            self::MODERATOR => 'Moderador',
            self::READER => 'Leitor',
        };
    }

    /**
     * Check if role has specific permissions.
     */
    public function canWrite(): bool
    {
        return in_array($this, [self::ADMIN, self::AUTHOR, self::MODERATOR]);
    }

    public function canModerate(): bool
    {
        return in_array($this, [self::ADMIN, self::MODERATOR]);
    }

    public function canAdmin(): bool
    {
        return $this === self::ADMIN;
    }
}
