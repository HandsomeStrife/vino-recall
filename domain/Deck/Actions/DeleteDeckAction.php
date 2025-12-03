<?php

declare(strict_types=1);

namespace Domain\Deck\Actions;

use Domain\Deck\Models\Deck;

class DeleteDeckAction
{
    public function execute(int $deckId): void
    {
        $deck = Deck::findOrFail($deckId);
        $deck->delete();
    }
}
