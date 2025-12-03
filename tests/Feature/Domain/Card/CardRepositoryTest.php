<?php

declare(strict_types=1);

use Domain\Card\Models\Card;
use Domain\Card\Models\CardReview;
use Domain\Card\Repositories\CardRepository;
use Domain\Deck\Models\Deck;
use Domain\User\Models\User;

test('get new cards for user returns cards not yet reviewed from enrolled decks only', function () {
    $user = User::factory()->create();
    $enrolledDeck = Deck::factory()->create();
    $notEnrolledDeck = Deck::factory()->create();
    
    // Enroll user in first deck
    $user->enrolledDecks()->attach($enrolledDeck->id, [
        'enrolled_at' => now(),
        'shortcode' => strtoupper(\Illuminate\Support\Str::random(8)),
    ]);
    
    $card1 = Card::factory()->create(['deck_id' => $enrolledDeck->id]);
    $card2 = Card::factory()->create(['deck_id' => $enrolledDeck->id]);
    $card3 = Card::factory()->create(['deck_id' => $enrolledDeck->id]);
    $card4 = Card::factory()->create(['deck_id' => $notEnrolledDeck->id]); // Not enrolled

    CardReview::factory()->create([
        'user_id' => $user->id,
        'card_id' => $card1->id,
    ]);

    $repository = new CardRepository;
    $newCards = $repository->getNewCardsForUser($user->id);

    expect($newCards->pluck('id')->toArray())->toContain($card2->id);
    expect($newCards->pluck('id')->toArray())->toContain($card3->id);
    expect($newCards->pluck('id')->toArray())->not->toContain($card1->id);
    expect($newCards->pluck('id')->toArray())->not->toContain($card4->id); // Not in enrolled deck
});

test('get by deck id returns cards for specific deck', function () {
    $deck1 = Deck::factory()->create();
    $deck2 = Deck::factory()->create();
    $card1 = Card::factory()->create(['deck_id' => $deck1->id]);
    $card2 = Card::factory()->create(['deck_id' => $deck1->id]);
    $card3 = Card::factory()->create(['deck_id' => $deck2->id]);

    $repository = new CardRepository;
    $cards = $repository->getByDeckId($deck1->id);

    expect($cards->pluck('id')->toArray())->toContain($card1->id);
    expect($cards->pluck('id')->toArray())->toContain($card2->id);
    expect($cards->pluck('id')->toArray())->not->toContain($card3->id);
});
