<?php

namespace App\Support;

use Carbon\Carbon;

class QuizLogic
{
    public static function calculatePercentage(float|int $score, float|int $totalPoints): float
    {
        if ($totalPoints <= 0) {
            return 0.0;
        }

        return round(($score / $totalPoints) * 100, 2);
    }

    public static function isLocked(?Carbon $lockedUntil): bool
    {
        if ($lockedUntil === null) {
            return false;
        }

        return $lockedUntil->isFuture();
    }

    public static function isExactMatch(string $givenAnswer, string $correctAnswer): bool
    {
        return trim($givenAnswer) === trim($correctAnswer);
    }


}