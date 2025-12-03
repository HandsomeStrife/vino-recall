<?php

declare(strict_types=1);

use Domain\Card\Models\Card;
use Domain\Card\Models\CardReview;
use Domain\Card\Repositories\CardRepository;
use Domain\Deck\Models\Deck;
use Domain\User\Models\User;

test('get new cards for user returns cards not yet reviewed', function () {
    $user = User::factory()->create();
    $deck = Deck::factory()->create();
    $card1 = Card::factory()->create(['deck_id' => $deck->id]);
    $card2 = Card::factory()->create(['deck_id' => $deck->id]);
    $card3 = Card::factory()->create(['deck_id' => $deck->id]);

    CardReview::factory()->create([
        'user_id' => $user->id,
        'card_id' => $card1->id,
    ]);

    $repository = new CardRepository;
    $newCards = $repository->getNewCardsForUser($user->id);

    expect($newCards->pluck('id')->toArray())->toContain($card2->id);
    expect($newCards->pluck('id')->toArray())->toContain($card3->id);
    expect($newCards->pluck('id')->toArray())->not->toContain($card1->id);
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
