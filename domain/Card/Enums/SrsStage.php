<?php

declare(strict_types=1);

namespace Domain\Card\Enums;

use DateInterval;

/**
 * Wine-themed SRS stages (0-9) for WaniKani-style spaced repetition.
 *
 * The integer stage is authoritative; the names are cosmetic.
 */
enum SrsStage: int
{
    case UNCORKED = 0;
    case HOBBYIST_I = 1;
    case HOBBYIST_II = 2;
    case STUDENT_I = 3;
    case STUDENT_II = 4;
    case CONNOISSEUR_I = 5;
    case CONNOISSEUR_II = 6;
    case PROFESSOR = 7;
    case SOMMELIER = 8;
    case WINE_GOD = 9;

    public const int STAGE_MIN = 0;

    public const int STAGE_MAX = 9;

    public const int MASTERED_THRESHOLD = 7;

    /**
     * Get the human-readable name for this stage.
     */
    public function getName(): string
    {
        return match ($this) {
            self::UNCORKED => 'Uncorked',
            self::HOBBYIST_I => 'Hobbyist I',
            self::HOBBYIST_II => 'Hobbyist II',
            self::STUDENT_I => 'Student I',
            self::STUDENT_II => 'Student II',
            self::CONNOISSEUR_I => 'Connoisseur I',
            self::CONNOISSEUR_II => 'Connoisseur II',
            self::PROFESSOR => 'Professor',
            self::SOMMELIER => 'Sommelier',
            self::WINE_GOD => 'Wine God',
        };
    }

    /**
     * Get the description for this stage.
     */
    public function getDescription(): string
    {
        return match ($this) {
            self::UNCORKED => 'Brand new, never reviewed',
            self::HOBBYIST_I => 'First exposure',
            self::HOBBYIST_II => 'Early casual learner',
            self::STUDENT_I => 'Structured study begins',
            self::STUDENT_II => 'Late short-term, concepts taking hold',
            self::CONNOISSEUR_I => 'Medium interval, solid familiarity',
            self::CONNOISSEUR_II => 'Long interval, very comfortable',
            self::PROFESSOR => 'Expert level recall',
            self::SOMMELIER => 'Deep, intuitive mastery',
            self::WINE_GOD => 'Burned - considered permanently learned',
        };
    }

    /**
     * Get the review interval for a given stage.
     * Returns null for stage 0 (not yet reviewed) and stage 9 (retired/burned).
     */
    public static function intervalForStage(int $stage): ?DateInterval
    {
        return match ($stage) {
            1 => new DateInterval('PT4H'),      // 4 hours
            2 => new DateInterval('PT8H'),      // 8 hours
            3 => new DateInterval('PT23H'),     // 23 hours
            4 => new DateInterval('PT47H'),     // 47 hours
            5 => new DateInterval('P7D'),       // 7 days
            6 => new DateInterval('P14D'),      // 14 days
            7 => new DateInterval('P30D'),      // 30 days
            8 => new DateInterval('P120D'),     // 120 days
            9 => null,                          // Wine God - retired, no further reviews
            default => null,                    // Stage 0 or invalid - not scheduled
        };
    }

    /**
     * Calculate the new stage after a correct answer.
     */
    public static function calculateNewStageOnCorrect(int $currentStage): int
    {
        return min($currentStage + 1, self::STAGE_MAX);
    }

    /**
     * Calculate the new stage after an incorrect answer.
     * Higher stages are punished more heavily.
     */
    public static function calculateNewStageOnIncorrect(int $currentStage): int
    {
        if ($currentStage >= 5) {
            // Connoisseur or higher - knock them down harder
            return max($currentStage - 2, 1);
        } elseif ($currentStage > 1) {
            // Mid learning area
            return $currentStage - 1;
        } else {
            // Stays in early learning (stage 0 or 1 -> 1)
            return 1;
        }
    }

    /**
     * Check if a stage is considered "mastered".
     */
    public static function isMastered(int $stage): bool
    {
        return $stage >= self::MASTERED_THRESHOLD;
    }

    /**
     * Get the SrsStage enum case from an integer stage value.
     */
    public static function fromStage(int $stage): self
    {
        return self::from(max(self::STAGE_MIN, min($stage, self::STAGE_MAX)));
    }
}
