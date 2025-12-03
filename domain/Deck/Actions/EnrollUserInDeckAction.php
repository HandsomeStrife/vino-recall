<?php

declare(strict_types=1);

namespace Domain\Deck\Actions;

use Domain\Deck\Models\Deck;
use Domain\User\Models\User;
use Illuminate\Support\Str;

class EnrollUserInDeckAction
{
    public function execute(int $user_id, int $deck_id): string
    {
        $user = User::findOrFail($user_id);
        $deck = Deck::findOrFail($deck_id);

        // Check if already enrolled
        $existing = $user->enrolledDecks()->where('deck_id', $deck_id)->first();
        
        if ($existing) {
            return $existing->pivot->shortcode;
        }

        // Generate unique shortcode
        $shortcode = $this->generateUniqueShortcode();

        $user->enrolledDecks()->attach($deck_id, [
            'enrolled_at' => now(),
            'shortcode' => $shortcode,
        ]);

        return $shortcode;
    }

    private function generateUniqueShortcode(): string
    {
        do {
            $shortcode = strtoupper(Str::random(8));
        } while (\DB::table('deck_user')->where('shortcode', $shortcode)->exists());

        return $shortcode;
    }
}

