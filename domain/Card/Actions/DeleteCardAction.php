<?php

declare(strict_types=1);

namespace Domain\Card\Actions;

use Domain\Card\Models\Card;

class DeleteCardAction
{
    public function execute(int $cardId): void
    {
        $card = Card::findOrFail($cardId);
        $card->delete();
    }
}
