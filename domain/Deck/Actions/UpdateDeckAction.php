<?php

declare(strict_types=1);

namespace Domain\Deck\Actions;

use Domain\Deck\Data\DeckData;
use Domain\Deck\Models\Deck;

class UpdateDeckAction
{
    public function execute(int $deckId, ?string $name = null, ?string $description = null, ?bool $is_active = null, ?string $category = null): DeckData
    {
        $deck = Deck::findOrFail($deckId);

        $updateData = [];

        if ($name !== null) {
            $updateData['name'] = $name;
        }

        if ($description !== null) {
            $updateData['description'] = $description;
        }

        if ($is_active !== null) {
            $updateData['is_active'] = $is_active;
        }

        if ($category !== null) {
            $updateData['category'] = $category;
        }

        $deck->update($updateData);

        return DeckData::fromModel($deck->fresh());
    }
}
