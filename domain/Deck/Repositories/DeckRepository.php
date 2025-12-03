<?php

declare(strict_types=1);

namespace Domain\Deck\Repositories;

use Domain\Deck\Data\DeckData;
use Domain\Deck\Models\Deck;

class DeckRepository
{
    /**
     * @return \Illuminate\Support\Collection<int, DeckData>
     */
    public function getAll(): \Illuminate\Support\Collection
    {
        return Deck::all()->map(fn (Deck $deck) => DeckData::fromModel($deck));
    }

    public function findById(int $id): ?DeckData
    {
        $deck = Deck::find($id);

        if ($deck === null) {
            return null;
        }

        return DeckData::fromModel($deck);
    }

    /**
     * @return \Illuminate\Support\Collection<int, DeckData>
     */
    public function getActive(): \Illuminate\Support\Collection
    {
        return Deck::where('is_active', true)->get()->map(fn (Deck $deck) => DeckData::fromModel($deck));
    }
}
