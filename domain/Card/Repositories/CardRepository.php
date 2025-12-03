<?php

declare(strict_types=1);

namespace Domain\Card\Repositories;

use Domain\Card\Data\CardData;
use Domain\Card\Models\Card;

class CardRepository
{
    /**
     * @return \Illuminate\Support\Collection<int, CardData>
     */
    public function getByDeckId(int $deckId): \Illuminate\Support\Collection
    {
        return Card::where('deck_id', $deckId)->get()->map(fn (Card $card) => CardData::fromModel($card));
    }

    public function findById(int $id): ?CardData
    {
        $card = Card::find($id);

        if ($card === null) {
            return null;
        }

        return CardData::fromModel($card);
    }

    /**
     * @return \Illuminate\Support\Collection<int, CardData>
     */
    public function getAll(): \Illuminate\Support\Collection
    {
        return Card::all()->map(fn (Card $card) => CardData::fromModel($card));
    }

    /**
     * Get cards that the user hasn't reviewed yet
     *
     * @return \Illuminate\Support\Collection<int, CardData>
     */
    public function getNewCardsForUser(int $userId): \Illuminate\Support\Collection
    {
        $reviewedCardIds = \Domain\Card\Models\CardReview::where('user_id', $userId)
            ->pluck('card_id')
            ->toArray();

        return Card::whereNotIn('id', $reviewedCardIds)
            ->get()
            ->map(fn (Card $card) => CardData::fromModel($card));
    }
}
