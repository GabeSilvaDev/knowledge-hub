<?php

namespace App\Helpers;

class ReadingTimeHelper
{
    /**
     * Calculate reading time in minutes.
     */
    public static function calculate(string $content, int $wordsPerMinute = 200): int
    {
        $wordCount = str_word_count(strip_tags($content));

        return max(1, (int) ceil($wordCount / $wordsPerMinute));
    }

    /**
     * Format reading time for display.
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
