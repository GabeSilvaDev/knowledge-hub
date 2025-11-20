<?php

namespace App\Helpers;

class ReadingTimeHelper
{
    /**
     * Calculate reading time in minutes.
     *
     * Calculates estimated reading time based on word count and reading speed.
     * Strips HTML tags before counting words.
     *
     * @param  string  $content  The article content
     * @param  int  $wordsPerMinute  Average reading speed (default: 200)
     * @return int Estimated reading time in minutes (minimum 1)
     */
    public static function calculate(string $content, int $wordsPerMinute = 200): int
    {
        $wordCount = str_word_count(strip_tags($content));

        return max(1, (int) ceil($wordCount / $wordsPerMinute));
    }

    /**
     * Format reading time for display.
     *
     * Converts reading time in minutes to a human-readable string in Portuguese.
     * Handles singular/plural forms and hour conversions.
     *
     * @param  int  $minutes  The reading time in minutes
     * @return string Formatted reading time string
     */
    public static function format(int $minutes): string
    {
        if ($minutes < 1) {
            return 'Menos de 1 minuto';
        }

        if ($minutes < 60) {
            return $minutes === 1 ? '1 minuto' : "{$minutes} minutos";
        }

        return self::formatHours($minutes);
    }

    /**
     * Format hours and minutes for display.
     *
     * Converts minutes to hours and remaining minutes format.
     * Used internally by the format method.
     *
     * @param  int  $minutes  Total minutes to format
     * @return string Formatted time string with hours and minutes
     */
    private static function formatHours(int $minutes): string
    {
        $hours = (int) floor($minutes / 60);
        $remainingMinutes = $minutes % 60;

        if ($remainingMinutes === 0) {
            return $hours === 1 ? '1 hora' : "{$hours} horas";
        }

        return "{$hours}h {$remainingMinutes}min";
    }
}
