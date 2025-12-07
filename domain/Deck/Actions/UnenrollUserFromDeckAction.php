<?php

declare(strict_types=1);

namespace Domain\Deck\Actions;

use Domain\Deck\Models\Deck;
use Domain\User\Models\User;

class UnenrollUserFromDeckAction
{
    /**
     * Unenroll a user from a deck. If the deck is a parent deck,
     * also unenroll the user from all child decks.
     */
    public function execute(int $userId, int $deckId): void
    {
        $user = User::findOrFail($userId);
        $deck = Deck::findOrFail($deckId);

        // Unenroll from the main deck
        $user->enrolledDecks()->detach($deckId);

        // If this is a collection, also unenroll from all children
        if ($deck->isCollection()) {
            $childIds = $deck->children()->pluck('id')->toArray();
            $user->enrolledDecks()->detach($childIds);
        }
    }
}
