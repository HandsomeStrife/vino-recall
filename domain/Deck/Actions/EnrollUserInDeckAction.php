<?php

declare(strict_types=1);

namespace Domain\Deck\Actions;

use Domain\Deck\Models\Deck;
use Domain\User\Models\User;
use Illuminate\Support\Str;

class EnrollUserInDeckAction
{
    /**
     * Enroll a user in a deck. If the deck is a parent deck,
     * also enroll the user in all child decks.
     *
     * @return string The shortcode for the primary deck enrollment
     */
    public function execute(int $user_id, int $deck_id): string
    {
        $user = User::findOrFail($user_id);
        $deck = Deck::findOrFail($deck_id);

        // Enroll in the main deck
        $shortcode = $this->enrollInSingleDeck($user, $deck);

        // If this is a collection, also enroll in all active children
        if ($deck->isCollection()) {
            $children = $deck->children()->where('is_active', true)->get();
            foreach ($children as $childDeck) {
                $this->enrollInSingleDeck($user, $childDeck);
            }
        }

        return $shortcode;
    }

    /**
     * Enroll a user in a single deck without cascading to children.
     */
    private function enrollInSingleDeck(User $user, Deck $deck): string
    {
        // Check if already enrolled
        $existing = $user->enrolledDecks()->where('deck_id', $deck->id)->first();

        if ($existing) {
            return $existing->pivot->shortcode;
        }

        // Generate unique shortcode
        $shortcode = $this->generateUniqueShortcode();

        $user->enrolledDecks()->attach($deck->id, [
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
