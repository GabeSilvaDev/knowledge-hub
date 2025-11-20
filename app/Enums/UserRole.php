<?php

namespace App\Enums;

/**
 * User Role Enum.
 *
 * Defines the available user roles and their permissions in the system.
 */
enum UserRole: string
{
    /** Administrator with full system access */
    case ADMIN = 'admin';

    /** Author who can create and edit articles */
    case AUTHOR = 'author';

    /** Moderator who can review and approve content */
    case MODERATOR = 'moderator';

    /** Reader with read-only access */
    case READER = 'reader';

    /**
     * Get all role values.
     *
     * Returns an array of all available role string values.
     *
     * @return array<string> Array of role values
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get role display name.
     *
     * Returns a localized, human-readable label for the role.
     *
     * @return string The display label in Portuguese
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
     * Check if role has write permissions.
     *
     * Determines if users with this role can create and edit content.
     *
     * @return bool True if role allows writing
     */
    public function canWrite(): bool
    {
        return in_array($this, [self::ADMIN, self::AUTHOR, self::MODERATOR]);
    }

    /**
     * Check if role has moderation permissions.
     *
     * Determines if users with this role can moderate content.
     *
     * @return bool True if role allows moderation
     */
    public function canModerate(): bool
    {
        return in_array($this, [self::ADMIN, self::MODERATOR]);
    }

    /**
     * Check if role has administration permissions.
     *
     * Determines if users with this role have full administrative access.
     *
     * @return bool True if role is admin
     */
    public function canAdmin(): bool
    {
        return $this === self::ADMIN;
    }
}
