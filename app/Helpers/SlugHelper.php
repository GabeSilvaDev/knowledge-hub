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
     * @param  class-string<Model>  $modelClass
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
     * @param  class-string<Model>  $modelClass
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
