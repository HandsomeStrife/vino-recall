<?php

declare(strict_types=1);

use Domain\Card\Actions\ReviewCardAction;
use Domain\Card\Enums\CardRating;
use Domain\Card\Models\Card;
use Domain\Card\Models\CardReview;
use Domain\Deck\Models\Deck;
use Domain\User\Models\User;

test('review card action creates new review for first time', function () {
    $user = User::factory()->create();
    $deck = Deck::factory()->create();
    $card = Card::factory()->create(['deck_id' => $deck->id]);
    $action = new ReviewCardAction;

    $reviewData = $action->execute($user->id, $card->id, CardRating::GOOD);

    expect($reviewData->user_id)->toBe($user->id);
    expect($reviewData->card_id)->toBe($card->id);
    expect($reviewData->rating)->toBe(CardRating::GOOD->value);

    $review = CardReview::where('user_id', $user->id)
        ->where('card_id', $card->id)
        ->first();

    expect($review)->not->toBeNull();
    expect((float) $review->ease_factor)->toBe(2.5);
});

test('review card action updates existing review', function () {
    $user = User::factory()->create();
    $deck = Deck::factory()->create();
    $card = Card::factory()->create(['deck_id' => $deck->id]);
    $action = new ReviewCardAction;

    $action->execute($user->id, $card->id, CardRating::GOOD);
    $reviewData = $action->execute($user->id, $card->id, CardRating::EASY);

    expect($reviewData->rating)->toBe(CardRating::EASY->value);
    expect($reviewData->ease_factor)->toBeGreaterThan(2.5);
});

test('review card action sets correct next review time for again rating', function () {
    $user = User::factory()->create();
    $deck = Deck::factory()->create();
    $card = Card::factory()->create(['deck_id' => $deck->id]);
    $action = new ReviewCardAction;

    $reviewData = $action->execute($user->id, $card->id, CardRating::AGAIN);

    $review = CardReview::find($reviewData->id);
    expect($review->next_review_at->diffInMinutes(now()))->toBeLessThan(2);
});

test('review card action sets correct next review time for hard rating', function () {
    $user = User::factory()->create();
    $deck = Deck::factory()->create();
    $card = Card::factory()->create(['deck_id' => $deck->id]);
    $action = new ReviewCardAction;

    $reviewData = $action->execute($user->id, $card->id, CardRating::HARD);

    $review = CardReview::find($reviewData->id);
    expect($review->next_review_at->diffInDays(now()->addDay()))->toBeLessThan(1);
});

test('review card action adjusts ease factor correctly', function () {
    $user = User::factory()->create();
    $deck = Deck::factory()->create();
    $card = Card::factory()->create(['deck_id' => $deck->id]);
    $action = new ReviewCardAction;

    $firstReview = $action->execute($user->id, $card->id, CardRating::AGAIN);
    expect($firstReview->ease_factor)->toBeLessThan(2.5);

    $secondReview = $action->execute($user->id, $card->id, CardRating::EASY);
    expect($secondReview->ease_factor)->toBeGreaterThan($firstReview->ease_factor);
});
