<?php

declare(strict_types=1);

namespace Domain\Card\Actions;

use Domain\Card\Models\CardReview;
use Domain\Card\Models\ReviewHistory;
use Domain\Deck\Models\Deck;
use Illuminate\Support\Facades\DB;

/**
 * Resets all SRS progress for a user on a specific deck.
 *
 * This action deletes:
 * - All CardReview records (SRS state) for cards in the deck
 * - All ReviewHistory records for cards in the deck
 *
 * After reset, the deck will appear as if the user has never studied it.
 */
class ResetDeckReviewsAction
{
    /**
     * Execute the deck reset.
     *
     * @return array{card_reviews_deleted: int, review_history_deleted: int}
     */
    public function execute(int $userId, int $deckId): array
    {
        // Verify deck exists
        $deck = Deck::findOrFail($deckId);

        // Get card IDs for this deck
        $cardIds = $deck->cards()->pluck('id')->toArray();

        if (empty($cardIds)) {
            return [
                'card_reviews_deleted' => 0,
                'review_history_deleted' => 0,
            ];
        }

        return DB::transaction(function () use ($userId, $cardIds) {
            // Delete CardReview records (SRS state)
            $cardReviewsDeleted = CardReview::where('user_id', $userId)
                ->whereIn('card_id', $cardIds)
                ->delete();

            // Delete ReviewHistory records
            $reviewHistoryDeleted = ReviewHistory::where('user_id', $userId)
                ->whereIn('card_id', $cardIds)
                ->delete();

            return [
                'card_reviews_deleted' => $cardReviewsDeleted,
                'review_history_deleted' => $reviewHistoryDeleted,
            ];
        });
    }
}
