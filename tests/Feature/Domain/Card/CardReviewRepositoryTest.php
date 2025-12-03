<?php

declare(strict_types=1);

use Domain\Card\Models\Card;
use Domain\Card\Models\CardReview;
use Domain\Card\Repositories\CardReviewRepository;
use Domain\Deck\Models\Deck;
use Domain\User\Models\User;

test('get due cards for user returns cards due for review', function () {
    $user = User::factory()->create();
    $deck = Deck::factory()->create();
    $card1 = Card::factory()->create(['deck_id' => $deck->id]);
    $card2 = Card::factory()->create(['deck_id' => $deck->id]);

    CardReview::factory()->create([
        'user_id' => $user->id,
        'card_id' => $card1->id,
        'next_review_at' => now()->subDay(),
    ]);

    CardReview::factory()->create([
        'user_id' => $user->id,
        'card_id' => $card2->id,
        'next_review_at' => now()->addDay(),
    ]);

    $repository = new CardReviewRepository;
    $dueCards = $repository->getDueCardsForUser($user->id);

    expect($dueCards->pluck('card_id')->toArray())->toContain($card1->id);
    expect($dueCards->pluck('card_id')->toArray())->not->toContain($card2->id);
});

test('get mastered cards count returns correct count', function () {
    $user = User::factory()->create();
    $deck = Deck::factory()->create();
    $card1 = Card::factory()->create(['deck_id' => $deck->id]);
    $card2 = Card::factory()->create(['deck_id' => $deck->id]);
    $card3 = Card::factory()->create(['deck_id' => $deck->id]);

    // Card1: mastered (ease_factor >= 2.0)
    CardReview::factory()->create([
        'user_id' => $user->id,
        'card_id' => $card1->id,
        'ease_factor' => 2.5,
    ]);

    // Card2: not mastered (ease_factor < 2.0)
    CardReview::factory()->create([
        'user_id' => $user->id,
        'card_id' => $card2->id,
        'ease_factor' => 1.5,
    ]);

    // Card3: mastered (ease_factor >= 2.0)
    CardReview::factory()->create([
        'user_id' => $user->id,
        'card_id' => $card3->id,
        'ease_factor' => 2.0,
    ]);

    $repository = new CardReviewRepository;
    $count = $repository->getMasteredCardsCount($user->id);

    expect($count)->toBe(2);
});

test('get current streak returns correct streak for consecutive days', function () {
    $user = User::factory()->create();
    $deck = Deck::factory()->create();
    $card1 = Card::factory()->create(['deck_id' => $deck->id]);
    $card2 = Card::factory()->create(['deck_id' => $deck->id]);
    $card3 = Card::factory()->create(['deck_id' => $deck->id]);

    CardReview::factory()->create([
        'user_id' => $user->id,
        'card_id' => $card1->id,
        'created_at' => now(),
    ]);

    CardReview::factory()->create([
        'user_id' => $user->id,
        'card_id' => $card2->id,
        'created_at' => now()->subDay(),
    ]);

    CardReview::factory()->create([
        'user_id' => $user->id,
        'card_id' => $card3->id,
        'created_at' => now()->subDays(2),
    ]);

    $repository = new CardReviewRepository;
    $streak = $repository->getCurrentStreak($user->id);

    expect($streak)->toBeGreaterThanOrEqual(2);
});

test('get recent activity returns most recent reviews', function () {
    $user = User::factory()->create();
    $deck = Deck::factory()->create();
    $card1 = Card::factory()->create(['deck_id' => $deck->id]);
    $card2 = Card::factory()->create(['deck_id' => $deck->id]);
    $card3 = Card::factory()->create(['deck_id' => $deck->id]);

    CardReview::factory()->create([
        'user_id' => $user->id,
        'card_id' => $card1->id,
        'created_at' => now()->subHours(1),
    ]);

    CardReview::factory()->create([
        'user_id' => $user->id,
        'card_id' => $card2->id,
        'created_at' => now()->subHours(2),
    ]);

    CardReview::factory()->create([
        'user_id' => $user->id,
        'card_id' => $card3->id,
        'created_at' => now()->subHours(3),
    ]);

    $repository = new CardReviewRepository;
    $activity = $repository->getRecentActivity($user->id, 2);

    expect($activity)->toHaveCount(2);
    expect($activity->first()->card_id)->toBe($card1->id);
});
