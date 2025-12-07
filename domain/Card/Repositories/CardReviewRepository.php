<?php

declare(strict_types=1);

namespace Domain\Card\Repositories;

use Carbon\Carbon;
use Domain\Card\Data\CardReviewData;
use Domain\Card\Data\ReviewHistoryData;
use Domain\Card\Enums\SrsStage;
use Domain\Card\Models\CardReview;
use Domain\Card\Models\ReviewHistory;
use Illuminate\Support\Collection;

class CardReviewRepository
{
    public function findUserCardReview(int $userId, int $cardId): ?CardReviewData
    {
        $review = CardReview::where('user_id', $userId)
            ->where('card_id', $cardId)
            ->first();

        if ($review === null) {
            return null;
        }

        return CardReviewData::fromModel($review);
    }

    /**
     * Get cards that are due for review (next_review_at <= now and stage < 9).
     *
     * @return Collection<int, CardReviewData>
     */
    public function getDueCardsForUser(int $userId): Collection
    {
        // Get enrolled deck IDs for the user
        $enrolledDeckIds = \Domain\User\Models\User::find($userId)
            ->enrolledDecks()
            ->pluck('decks.id')
            ->toArray();

        return CardReview::where('user_id', $userId)
            ->where('next_review_at', '<=', now())
            ->where('srs_stage', '<', SrsStage::STAGE_MAX) // Not Wine God (retired)
            ->whereHas('card', function ($query) use ($enrolledDeckIds) {
                $query->whereIn('deck_id', $enrolledDeckIds);
            })
            ->get()
            ->map(fn (CardReview $review) => CardReviewData::fromModel($review));
    }

    /**
     * Get all review states for a user from enrolled decks.
     *
     * @return Collection<int, CardReviewData>
     */
    public function getUserReviews(int $userId): Collection
    {
        // Get enrolled deck IDs for the user
        $enrolledDeckIds = \Domain\User\Models\User::find($userId)
            ->enrolledDecks()
            ->pluck('decks.id')
            ->toArray();

        return CardReview::where('user_id', $userId)
            ->whereHas('card', function ($query) use ($enrolledDeckIds) {
                $query->whereIn('deck_id', $enrolledDeckIds);
            })
            ->get()
            ->map(fn (CardReview $review) => CardReviewData::fromModel($review));
    }

    /**
     * Get count of mastered cards (srs_stage >= MASTERED_THRESHOLD) from enrolled decks.
     */
    public function getMasteredCardsCount(int $userId): int
    {
        // Get enrolled deck IDs for the user
        $enrolledDeckIds = \Domain\User\Models\User::find($userId)
            ->enrolledDecks()
            ->pluck('decks.id')
            ->toArray();

        return CardReview::where('user_id', $userId)
            ->where('srs_stage', '>=', SrsStage::MASTERED_THRESHOLD)
            ->whereHas('card', function ($query) use ($enrolledDeckIds) {
                $query->whereIn('deck_id', $enrolledDeckIds);
            })
            ->count();
    }

    /**
     * Get recent mistakes (incorrect answers) for a user from review history.
     *
     * @return Collection<int, ReviewHistoryData>
     */
    public function getMistakes(int $userId, int $limit = 10): Collection
    {
        return ReviewHistory::where('user_id', $userId)
            ->where('is_correct', false)
            ->where('is_practice', false)
            ->orderBy('reviewed_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(fn (ReviewHistory $history) => ReviewHistoryData::fromModel($history));
    }

    /**
     * Calculate accuracy rate from review history.
     * Accuracy = correct_reviews / total_reviews over a time window.
     * Excludes practice reviews.
     */
    public function getAccuracy(int $userId, ?int $deckId = null, ?Carbon $since = null): float
    {
        $query = ReviewHistory::where('user_id', $userId)
            ->where('is_practice', false);

        if ($deckId !== null) {
            $query->whereHas('card', function ($q) use ($deckId) {
                $q->where('deck_id', $deckId);
            });
        }

        if ($since !== null) {
            $query->where('reviewed_at', '>=', $since);
        }

        $totalReviews = $query->count();

        if ($totalReviews === 0) {
            return 0.0;
        }

        $correctReviews = (clone $query)->where('is_correct', true)->count();

        return round(($correctReviews / $totalReviews) * 100, 1);
    }

    /**
     * Calculate mastery rate for a user's deck.
     * Mastery = cards with srs_stage >= MASTERED_THRESHOLD / total cards.
     */
    public function getMasteryRate(int $userId, int $deckId, int $totalCards): float
    {
        if ($totalCards === 0) {
            return 0.0;
        }

        $masteredCount = CardReview::where('user_id', $userId)
            ->where('srs_stage', '>=', SrsStage::MASTERED_THRESHOLD)
            ->whereHas('card', function ($q) use ($deckId) {
                $q->where('deck_id', $deckId);
            })
            ->count();

        return round(($masteredCount / $totalCards) * 100, 1);
    }

    /**
     * Calculate progress for a user's deck.
     * Progress = average(srs_stage / STAGE_MAX) * 100.
     *
     * Cards not in card_reviews are at stage 0.
     */
    public function getProgress(int $userId, int $deckId, int $totalCards): float
    {
        if ($totalCards === 0) {
            return 0.0;
        }

        // Sum of all stages for this deck
        $stageSum = CardReview::where('user_id', $userId)
            ->whereHas('card', function ($q) use ($deckId) {
                $q->where('deck_id', $deckId);
            })
            ->sum('srs_stage');

        // Each card contributes stage/9, averaged across total cards
        // progress = (sum(stage) / 9) / total_cards * 100
        return round(($stageSum / SrsStage::STAGE_MAX / $totalCards) * 100, 1);
    }

    /**
     * Get current streak (consecutive days with at least one non-practice review).
     */
    public function getCurrentStreak(int $userId): int
    {
        $reviewDates = ReviewHistory::where('user_id', $userId)
            ->where('is_practice', false)
            ->selectRaw('DATE(reviewed_at) as review_date')
            ->distinct()
            ->orderBy('review_date', 'desc')
            ->pluck('review_date')
            ->map(fn ($date) => Carbon::parse($date)->format('Y-m-d'))
            ->unique()
            ->values()
            ->toArray();

        if (empty($reviewDates)) {
            return 0;
        }

        $today = now()->format('Y-m-d');
        $yesterday = now()->copy()->subDay()->format('Y-m-d');

        // If last review was not today or yesterday, streak is broken
        if ($reviewDates[0] !== $today && $reviewDates[0] !== $yesterday) {
            return 0;
        }

        $streak = 0;
        $currentDate = now()->copy();

        foreach ($reviewDates as $reviewDateStr) {
            $reviewDate = Carbon::parse($reviewDateStr);
            $expectedDate = $currentDate->format('Y-m-d');

            if ($reviewDate->format('Y-m-d') === $expectedDate) {
                $streak++;
                $currentDate->subDay();
            } elseif ($reviewDate->format('Y-m-d') === $currentDate->copy()->subDay()->format('Y-m-d')) {
                // Allow one day gap (yesterday)
                $streak++;
                $currentDate->subDay();
            } else {
                break;
            }
        }

        return $streak;
    }

    /**
     * Get recent activity (last N reviews) from review history for enrolled decks.
     *
     * @return Collection<int, ReviewHistoryData>
     */
    public function getRecentActivity(int $userId, int $limit = 5): Collection
    {
        // Get enrolled deck IDs for the user
        $enrolledDeckIds = \Domain\User\Models\User::find($userId)
            ->enrolledDecks()
            ->pluck('decks.id')
            ->toArray();

        return ReviewHistory::where('user_id', $userId)
            ->whereHas('card', function ($query) use ($enrolledDeckIds) {
                $query->whereIn('deck_id', $enrolledDeckIds);
            })
            ->orderBy('reviewed_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(fn (ReviewHistory $history) => ReviewHistoryData::fromModel($history));
    }

    /**
     * Get count of cards reviewed (cards with any SRS state) for a deck.
     */
    public function getReviewedCardsCount(int $userId, int $deckId): int
    {
        return CardReview::where('user_id', $userId)
            ->whereHas('card', function ($q) use ($deckId) {
                $q->where('deck_id', $deckId);
            })
            ->count();
    }

    /**
     * Get the SRS stage distribution for a deck.
     *
     * @return array<int, int> Stage => count
     */
    public function getStageDistribution(int $userId, int $deckId): array
    {
        $distribution = [];
        for ($stage = 0; $stage <= SrsStage::STAGE_MAX; $stage++) {
            $distribution[$stage] = 0;
        }

        $stages = CardReview::where('user_id', $userId)
            ->whereHas('card', function ($q) use ($deckId) {
                $q->where('deck_id', $deckId);
            })
            ->pluck('srs_stage');

        foreach ($stages as $stage) {
            $distribution[$stage]++;
        }

        return $distribution;
    }
}
