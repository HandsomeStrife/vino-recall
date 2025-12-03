<?php

declare(strict_types=1);

namespace Domain\Deck\Actions;

use Domain\User\Models\User;

class UnenrollUserFromDeckAction
{
    public function execute(int $userId, int $deckId): void
    {
        $user = User::findOrFail($userId);

        $user->enrolledDecks()->detach($deckId);
    }
}

