<?php

declare(strict_types=1);

namespace Domain\Card\Actions;

use Domain\Card\Data\CardReviewData;
use Domain\Card\Enums\CardRating;
use Domain\Card\Models\Card;
use Domain\Card\Models\CardReview;

class ReviewCardAction
{
    private const INITIAL_EASE_FACTOR = 2.5;

    private const MIN_EASE_FACTOR = 1.3;

    private const INCORRECT_INTERVAL_HOURS = 4;

    /**
     * @param array<string>|null $selectedAnswers Array of selected answer strings for multi-answer support
     */
    public function execute(int $userId, int $cardId, ?array $selectedAnswers = null, bool $isPractice = false): CardReviewData
    {
        $card = Card::findOrFail($cardId);

        // Determine if answer is correct
        $isCorrect = $this->isAnswerCorrect($card, $selectedAnswers);
        $rating = $isCorrect ? CardRating::CORRECT : CardRating::INCORRECT;

        // Store selected answers as JSON for review history
        $selectedAnswerJson = $selectedAnswers !== null ? json_encode($selectedAnswers) : null;

        $review = CardReview::where('user_id', $userId)
            ->where('card_id', $cardId)
            ->first();

        if ($isPractice) {
            // For practice sessions, if no SRS review exists yet, create a practice record
            // Otherwise, just return a virtual practice response without persisting
            // (to avoid unique constraint violation on user_id + card_id)
            if ($review === null) {
                $practiceReview = CardReview::create([
                    'user_id' => $userId,
                    'card_id' => $cardId,
                    'rating' => $rating->value,
                    'is_correct' => $isCorrect,
                    'is_practice' => true,
                    'selected_answer' => $selectedAnswerJson,
                    'ease_factor' => self::INITIAL_EASE_FACTOR,
                    'next_review_at' => now()->addDay(),
                ]);

                return CardReviewData::fromModel($practiceReview);
            }

            // Return a virtual practice response based on existing review
            // This doesn't persist a new record but provides feedback
            return new CardReviewData(
                id: $review->id,
                user_id: $userId,
                card_id: $cardId,
                rating: $rating->value,
                is_correct: $isCorrect,
                is_practice: true,
                selected_answer: $selectedAnswerJson,
                next_review_at: $review->next_review_at->toDateTimeString(),
                ease_factor: (string) $review->ease_factor,
                created_at: now()->toDateTimeString(),
                updated_at: now()->toDateTimeString(),
            );
        }

        $easeFactor = $review?->ease_factor ?? self::INITIAL_EASE_FACTOR;

        // Calculate next interval based on correctness and review history
        if ($isCorrect) {
            // Increase ease factor for correct answers
            $easeFactor = min($easeFactor + 0.1, 3.0);

            // Calculate exponentially increasing intervals
            if ($review === null) {
                // First review correct: 1 day
                $nextReviewAt = now()->addDay();
            } else {
                // Subsequent reviews: multiply previous interval by ease factor
                $daysSinceLastReview = $review->created_at->diffInDays(now());
                $nextIntervalDays = max(1, (int) ($daysSinceLastReview * $easeFactor));
                $nextReviewAt = now()->addDays($nextIntervalDays);
            }
        } else {
            // Decrease ease factor for incorrect answers
            $easeFactor = max($easeFactor - 0.2, self::MIN_EASE_FACTOR);

            // Incorrect answers: review in 4 hours
            $nextReviewAt = now()->addHours(self::INCORRECT_INTERVAL_HOURS);
        }

        if ($review === null) {
            $review = CardReview::create([
                'user_id' => $userId,
                'card_id' => $cardId,
                'rating' => $rating->value,
                'is_correct' => $isCorrect,
                'is_practice' => false,
                'selected_answer' => $selectedAnswerJson,
                'ease_factor' => $easeFactor,
                'next_review_at' => $nextReviewAt,
            ]);
        } else {
            $review->update([
                'rating' => $rating->value,
                'is_correct' => $isCorrect,
                'is_practice' => false,
                'selected_answer' => $selectedAnswerJson,
                'ease_factor' => $easeFactor,
                'next_review_at' => $nextReviewAt,
            ]);
        }

        return CardReviewData::fromModel($review->fresh());
    }

    /**
     * Check if the selected answers are correct (all-or-nothing for multi-answer).
     *
     * @param array<string>|null $selectedAnswers
     */
    private function isAnswerCorrect(Card $card, ?array $selectedAnswers): bool
    {
        // All cards are multiple choice - validate the answer
        if ($selectedAnswers === null || count($selectedAnswers) === 0) {
            return false;
        }

        if ($card->correct_answer_indices === null || $card->answer_choices === null) {
            return false;
        }

        $answerChoices = json_decode($card->answer_choices, true);
        $correctIndices = json_decode($card->correct_answer_indices, true);

        if (!is_array($answerChoices) || !is_array($correctIndices)) {
            return false;
        }

        // Get the correct answers as strings
        $correctAnswers = [];
        foreach ($correctIndices as $index) {
            if (isset($answerChoices[$index])) {
                $correctAnswers[] = $answerChoices[$index];
            }
        }

        // Sort both arrays for comparison
        $sortedSelected = $selectedAnswers;
        $sortedCorrect = $correctAnswers;
        sort($sortedSelected);
        sort($sortedCorrect);

        // All-or-nothing: user must select exactly the correct answers
        return $sortedSelected === $sortedCorrect;
    }
}
