<?php

declare(strict_types=1);

namespace Domain\Card\Actions;

use Domain\Card\Data\CardReviewData;
use Domain\Card\Enums\CardRating;
use Domain\Card\Models\CardReview;

class ReviewCardAction
{
    private const INITIAL_EASE_FACTOR = 2.5;

    private const MIN_EASE_FACTOR = 1.3;

    private const EASE_FACTOR_CHANGE = [
        CardRating::AGAIN->value => -0.2,
        CardRating::HARD->value => -0.15,
        CardRating::GOOD->value => 0.0,
        CardRating::EASY->value => 0.15,
    ];

    private const INTERVAL_DAYS = [
        CardRating::AGAIN->value => 0, // 1 minute (handled separately)
        CardRating::HARD->value => 1,
        CardRating::GOOD->value => 3,
        CardRating::EASY->value => 7,
    ];

    public function execute(int $userId, int $cardId, CardRating $rating, ?string $selectedAnswer = null): CardReviewData
    {
        $review = CardReview::where('user_id', $userId)
            ->where('card_id', $cardId)
            ->first();

        $easeFactor = $review?->ease_factor ?? self::INITIAL_EASE_FACTOR;
        $easeFactor += self::EASE_FACTOR_CHANGE[$rating->value];
        $easeFactor = max($easeFactor, self::MIN_EASE_FACTOR);

        $intervalDays = self::INTERVAL_DAYS[$rating->value];
        $nextReviewAt = match ($rating) {
            CardRating::AGAIN => now()->addMinute(),
            default => now()->addDays($intervalDays),
        };

        if ($review === null) {
            $review = CardReview::create([
                'user_id' => $userId,
                'card_id' => $cardId,
                'rating' => $rating->value,
                'selected_answer' => $selectedAnswer,
                'ease_factor' => $easeFactor,
                'next_review_at' => $nextReviewAt,
            ]);
        } else {
            $review->update([
                'rating' => $rating->value,
                'selected_answer' => $selectedAnswer,
                'ease_factor' => $easeFactor,
                'next_review_at' => $nextReviewAt,
            ]);
        }

        return CardReviewData::fromModel($review->fresh());
    }
}
