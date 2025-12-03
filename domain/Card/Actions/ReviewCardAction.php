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

    public function execute(int $userId, int $cardId, ?string $selectedAnswer = null, bool $isPractice = false): CardReviewData
    {
        $card = Card::findOrFail($cardId);
        
        // Determine if answer is correct
        $isCorrect = $this->isAnswerCorrect($card, $selectedAnswer);
        $rating = $isCorrect ? CardRating::CORRECT : CardRating::INCORRECT;

        $review = CardReview::where('user_id', $userId)
            ->where('card_id', $cardId)
            ->first();

        if ($isPractice) {
            // For practice sessions, record the review but don't affect SRS
            CardReview::create([
                'user_id' => $userId,
                'card_id' => $cardId,
                'rating' => $rating->value,
                'is_correct' => $isCorrect,
                'is_practice' => true,
                'selected_answer' => $selectedAnswer,
                'ease_factor' => $review?->ease_factor ?? self::INITIAL_EASE_FACTOR,
                'next_review_at' => $review?->next_review_at ?? now()->addDay(),
            ]);

            // Return the existing review or create a temporary one
            return CardReviewData::fromModel(
                CardReview::where('user_id', $userId)
                    ->where('card_id', $cardId)
                    ->where('is_practice', true)
                    ->latest()
                    ->first()
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
                'selected_answer' => $selectedAnswer,
                'ease_factor' => $easeFactor,
                'next_review_at' => $nextReviewAt,
            ]);
        } else {
            $review->update([
                'rating' => $rating->value,
                'is_correct' => $isCorrect,
                'is_practice' => false,
                'selected_answer' => $selectedAnswer,
                'ease_factor' => $easeFactor,
                'next_review_at' => $nextReviewAt,
            ]);
        }

        return CardReviewData::fromModel($review->fresh());
    }

    private function isAnswerCorrect(Card $card, ?string $selectedAnswer): bool
    {
        // For multiple choice cards
        if ($card->card_type === 'multiple_choice') {
            if ($selectedAnswer === null || $card->correct_answer_index === null || $card->answer_choices === null) {
                return false;
            }

            $answerChoices = json_decode($card->answer_choices, true);
            if (!is_array($answerChoices) || !isset($answerChoices[$card->correct_answer_index])) {
                return false;
            }

            return $selectedAnswer === $answerChoices[$card->correct_answer_index];
        }

        // For traditional cards, we cannot auto-determine correctness
        // So we'll mark it as correct by default (user self-assessment)
        return true;
    }
}
