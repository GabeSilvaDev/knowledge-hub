<?php

namespace App\Helpers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SlugHelper
{
    /**
     * Generate a unique slug for a model.
     *
     * Creates a URL-friendly slug from the title and ensures uniqueness by appending
     * a numeric suffix if the slug already exists in the database.
     *
     * @param  string  $title  The title to generate slug from
     * @param  class-string<Model>  $modelClass  The model class to check uniqueness against
     * @param  string|null  $excludeId  Optional ID to exclude from uniqueness check (for updates)
     * @return string The unique slug
     */
    public static function generateUniqueSlug(string $title, string $modelClass, ?string $excludeId = null): string
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $counter = 1;

        while (self::slugExists($slug, $modelClass, $excludeId)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Check if slug exists in model.
     *
     * Verifies if a slug already exists in the specified model's collection.
     * Optionally excludes a specific ID (useful for updates).
     *
     * @param  string  $slug  The slug to check
     * @param  class-string<Model>  $modelClass  The model class to check against
     * @param  string|null  $excludeId  Optional ID to exclude from the check
     * @return bool True if slug exists (excluding the excluded ID)
     */
    private static function slugExists(string $slug, string $modelClass, ?string $excludeId = null): bool
    {
        /** @var Builder<Model> $query */
        $query = $modelClass::where('slug', $slug);

        if ($excludeId) {
            $query->where('_id', '!=', $excludeId);
        }

        return $query->exists();
    }
}
