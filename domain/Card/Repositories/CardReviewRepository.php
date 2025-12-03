<?php

declare(strict_types=1);

namespace Domain\Card\Repositories;

use Domain\Card\Data\CardReviewData;
use Domain\Card\Models\CardReview;

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
     * @return \Illuminate\Support\Collection<int, CardReviewData>
     */
    public function getDueCardsForUser(int $userId): \Illuminate\Support\Collection
    {
        return CardReview::where('user_id', $userId)
            ->where('next_review_at', '<=', now())
            ->get()
            ->map(fn (CardReview $review) => CardReviewData::fromModel($review));
    }

    /**
     * @return \Illuminate\Support\Collection<int, CardReviewData>
     */
    public function getUserReviews(int $userId): \Illuminate\Support\Collection
    {
        return CardReview::where('user_id', $userId)
            ->get()
            ->map(fn (CardReview $review) => CardReviewData::fromModel($review));
    }

    /**
     * Get count of mastered cards (cards with ease_factor >= 2.0)
     * Since there's a unique constraint on (user_id, card_id), each card can only have one review record.
     * A card is considered "mastered" if it has been reviewed and has a high ease_factor.
     */
    public function getMasteredCardsCount(int $userId): int
    {
        return CardReview::where('user_id', $userId)
            ->where('ease_factor', '>=', 2.0)
            ->count();
    }

    /**
     * Get current streak (consecutive days with at least one review)
     */
    public function getCurrentStreak(int $userId): int
    {
        $reviewDates = CardReview::where('user_id', $userId)
            ->selectRaw('DATE(created_at) as review_date')
            ->distinct()
            ->orderBy('review_date', 'desc')
            ->pluck('review_date')
            ->map(fn ($date) => \Carbon\Carbon::parse($date)->format('Y-m-d'))
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
            $reviewDate = \Carbon\Carbon::parse($reviewDateStr);
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
     * Get recent activity (last 5 reviews)
     *
     * @return \Illuminate\Support\Collection<int, CardReviewData>
     */
    public function getRecentActivity(int $userId, int $limit = 5): \Illuminate\Support\Collection
    {
        return CardReview::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(fn (CardReview $review) => CardReviewData::fromModel($review));
    }
}
