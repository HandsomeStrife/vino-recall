<?php

declare(strict_types=1);

use Domain\Card\Actions\ResetDeckReviewsAction;
use Domain\Card\Enums\CardType;
use Domain\Card\Models\Card;
use Domain\Card\Models\CardReview;
use Domain\Card\Models\ReviewHistory;
use Domain\Deck\Models\Deck;
use Domain\User\Models\User;

test('reset deck reviews deletes all card reviews for user and deck', function () {
    $user = User::factory()->create();
    $deck = Deck::factory()->create();

    // Create cards in the deck
    $cards = Card::factory()->count(3)->create([
        'deck_id' => $deck->id,
        'card_type' => CardType::MULTIPLE_CHOICE->value,
    ]);

    // Create card reviews for the user
    foreach ($cards as $card) {
        CardReview::create([
            'user_id' => $user->id,
            'card_id' => $card->id,
            'srs_stage' => 3,
            'next_review_at' => now()->addDay(),
        ]);
    }

    expect(CardReview::where('user_id', $user->id)->count())->toBe(3);

    $action = new ResetDeckReviewsAction;
    $result = $action->execute($user->id, $deck->id);

    expect($result['card_reviews_deleted'])->toBe(3);
    expect(CardReview::where('user_id', $user->id)->count())->toBe(0);
});

test('reset deck reviews deletes all review history for user and deck', function () {
    $user = User::factory()->create();
    $deck = Deck::factory()->create();

    $card = Card::factory()->create([
        'deck_id' => $deck->id,
        'card_type' => CardType::MULTIPLE_CHOICE->value,
    ]);

    // Create review history entries
    ReviewHistory::factory()->count(5)->create([
        'user_id' => $user->id,
        'card_id' => $card->id,
    ]);

    expect(ReviewHistory::where('user_id', $user->id)->count())->toBe(5);

    $action = new ResetDeckReviewsAction;
    $result = $action->execute($user->id, $deck->id);

    expect($result['review_history_deleted'])->toBe(5);
    expect(ReviewHistory::where('user_id', $user->id)->count())->toBe(0);
});

test('reset deck reviews only affects the specified deck', function () {
    $user = User::factory()->create();
    $deck1 = Deck::factory()->create();
    $deck2 = Deck::factory()->create();

    $card1 = Card::factory()->create([
        'deck_id' => $deck1->id,
        'card_type' => CardType::MULTIPLE_CHOICE->value,
    ]);
    $card2 = Card::factory()->create([
        'deck_id' => $deck2->id,
        'card_type' => CardType::MULTIPLE_CHOICE->value,
    ]);

    CardReview::create([
        'user_id' => $user->id,
        'card_id' => $card1->id,
        'srs_stage' => 3,
        'next_review_at' => now()->addDay(),
    ]);
    CardReview::create([
        'user_id' => $user->id,
        'card_id' => $card2->id,
        'srs_stage' => 5,
        'next_review_at' => now()->addDays(7),
    ]);

    expect(CardReview::where('user_id', $user->id)->count())->toBe(2);

    $action = new ResetDeckReviewsAction;
    $result = $action->execute($user->id, $deck1->id);

    expect($result['card_reviews_deleted'])->toBe(1);
    expect(CardReview::where('user_id', $user->id)->count())->toBe(1);
    expect(CardReview::where('user_id', $user->id)->where('card_id', $card2->id)->exists())->toBeTrue();
});

test('reset deck reviews only affects the specified user', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $deck = Deck::factory()->create();

    $card = Card::factory()->create([
        'deck_id' => $deck->id,
        'card_type' => CardType::MULTIPLE_CHOICE->value,
    ]);

    CardReview::create([
        'user_id' => $user1->id,
        'card_id' => $card->id,
        'srs_stage' => 3,
        'next_review_at' => now()->addDay(),
    ]);
    CardReview::create([
        'user_id' => $user2->id,
        'card_id' => $card->id,
        'srs_stage' => 5,
        'next_review_at' => now()->addDays(7),
    ]);

    $action = new ResetDeckReviewsAction;
    $result = $action->execute($user1->id, $deck->id);

    expect($result['card_reviews_deleted'])->toBe(1);
    expect(CardReview::where('user_id', $user1->id)->count())->toBe(0);
    expect(CardReview::where('user_id', $user2->id)->count())->toBe(1);
});

test('reset deck reviews handles empty deck gracefully', function () {
    $user = User::factory()->create();
    $deck = Deck::factory()->create();

    // No cards in deck

    $action = new ResetDeckReviewsAction;
    $result = $action->execute($user->id, $deck->id);

    expect($result['card_reviews_deleted'])->toBe(0);
    expect($result['review_history_deleted'])->toBe(0);
});

test('reset deck reviews handles deck with no reviews gracefully', function () {
    $user = User::factory()->create();
    $deck = Deck::factory()->create();

    // Create cards but no reviews
    Card::factory()->count(3)->create([
        'deck_id' => $deck->id,
        'card_type' => CardType::MULTIPLE_CHOICE->value,
    ]);

    $action = new ResetDeckReviewsAction;
    $result = $action->execute($user->id, $deck->id);

    expect($result['card_reviews_deleted'])->toBe(0);
    expect($result['review_history_deleted'])->toBe(0);
});
