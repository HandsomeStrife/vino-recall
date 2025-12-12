<?php

declare(strict_types=1);

namespace Domain\Card\Actions;

use Domain\Card\Models\Card;
use Domain\Card\Models\CardReport;

class CreateCardReportAction
{
    public function execute(int $cardId, int $userId, string $message): CardReport
    {
        $card = Card::findOrFail($cardId);

        return CardReport::create([
            'card_id' => $cardId,
            'user_id' => $userId,
            'card_shortcode' => $card->shortcode,
            'message' => $message,
            'status' => 'pending',
        ]);
    }
}
