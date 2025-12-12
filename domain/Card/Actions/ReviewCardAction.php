<?php

declare(strict_types=1);

namespace Domain\Card\Actions;

use Domain\Card\Data\CardReviewData;
use Domain\Card\Data\ReviewHistoryData;
use Domain\Card\Enums\SrsStage;
use Domain\Card\Models\Card;
use Domain\Card\Models\CardReview;
use Domain\Card\Models\ReviewHistory;

/**
 * Records a card review and updates SRS state using WaniKani-style stage progression.
 */
class ReviewCardAction
{
    /**
     * Execute a card review.
     *
     * @param  array<string>|null  $selectedAnswers  Array of selected answer strings for multi-answer support
     */
    public function execute(int $userId, int $cardId, ?array $selectedAnswers = null, bool $isPractice = false): CardReviewData
    {
        $card = Card::findOrFail($cardId);

        // Determine if answer is correct
        $isCorrect = $this->isAnswerCorrect($card, $selectedAnswers);

        // Get existing SRS state (or default to stage 0)
        $review = CardReview::where('user_id', $userId)
            ->where('card_id', $cardId)
            ->first();

        $currentStage = $review?->srs_stage ?? SrsStage::STAGE_MIN;

        // Calculate new stage based on correctness
        $newStage = $isCorrect
            ? SrsStage::calculateNewStageOnCorrect($currentStage)
            : SrsStage::calculateNewStageOnIncorrect($currentStage);

        // Log the review to history (always, even for practice)
        $this->logReviewHistory($userId, $cardId, $isCorrect, $currentStage, $newStage, $isPractice);

        // For practice mode: don't update SRS state
        if ($isPractice) {
            // If no review exists yet, create one at stage 0 (but don't advance)
            // This ensures the card shows as "seen" but practice doesn't affect progression
            if ($review === null) {
                $review = CardReview::create([
                    'user_id' => $userId,
                    'card_id' => $cardId,
                    'srs_stage' => SrsStage::STAGE_MIN,
                    'next_review_at' => null,
                ]);
            }

            return CardReviewData::fromModel($review);
        }

        // Calculate next review time based on new stage
        $nextReviewAt = $this->calculateNextReviewAt($newStage);

        // Create or update the SRS state
        if ($review === null) {
            $review = CardReview::create([
                'user_id' => $userId,
                'card_id' => $cardId,
                'srs_stage' => $newStage,
                'next_review_at' => $nextReviewAt,
            ]);
        } else {
            $review->update([
                'srs_stage' => $newStage,
                'next_review_at' => $nextReviewAt,
            ]);
        }

        return CardReviewData::fromModel($review->fresh());
    }

    /**
     * Log a review event to the history table.
     */
    private function logReviewHistory(
        int $userId,
        int $cardId,
        bool $isCorrect,
        int $previousStage,
        int $newStage,
        bool $isPractice
    ): ReviewHistoryData {
        $history = ReviewHistory::create([
            'user_id' => $userId,
            'card_id' => $cardId,
            'is_correct' => $isCorrect,
            'previous_stage' => $previousStage,
            'new_stage' => $isPractice ? $previousStage : $newStage, // Practice doesn't change stage
            'is_practice' => $isPractice,
            'reviewed_at' => now(),
        ]);

        return ReviewHistoryData::fromModel($history);
    }

    /**
     * Calculate the next review datetime based on the new stage.
     */
    private function calculateNextReviewAt(int $stage): ?\Carbon\Carbon
    {
        $interval = SrsStage::intervalForStage($stage);

        if ($interval === null) {
            // Stage 0 or 9 (Wine God) - no scheduled review
            return null;
        }

        return now()->add($interval);
    }

    /**
     * Check if the selected answers are correct (all-or-nothing for multi-answer).
     *
     * @param  array<string>|null  $selectedAnswers
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

        if (! is_array($answerChoices) || ! is_array($correctIndices)) {
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
