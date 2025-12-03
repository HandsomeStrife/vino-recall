<?php

declare(strict_types=1);

use Domain\Card\Models\Card;
use Domain\Card\Models\CardReview;
use Domain\Card\Repositories\CardReviewRepository;
use Domain\Deck\Models\Deck;
use Domain\User\Models\User;

test('get due cards for user returns cards due for review from enrolled decks only', function () {
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
    $card3 = Card::factory()->create(['deck_id' => $notEnrolledDeck->id]);

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
    
    CardReview::factory()->create([
        'user_id' => $user->id,
        'card_id' => $card3->id,
        'next_review_at' => now()->subDay(),
    ]);

    $repository = new CardReviewRepository;
    $dueCards = $repository->getDueCardsForUser($user->id);

    expect($dueCards->pluck('card_id')->toArray())->toContain($card1->id);
    expect($dueCards->pluck('card_id')->toArray())->not->toContain($card2->id);
    expect($dueCards->pluck('card_id')->toArray())->not->toContain($card3->id); // Not in enrolled deck
});

test('get mastered cards count returns correct count from enrolled decks only', function () {
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
    $card4 = Card::factory()->create(['deck_id' => $notEnrolledDeck->id]);

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
    
    // Card4: mastered but not in enrolled deck
    CardReview::factory()->create([
        'user_id' => $user->id,
        'card_id' => $card4->id,
        'ease_factor' => 2.5,
    ]);

    $repository = new CardReviewRepository;
    $count = $repository->getMasteredCardsCount($user->id);

    expect($count)->toBe(2); // Only cards from enrolled deck
});

test('get current streak returns correct streak for consecutive days', function () {
    $user = User::factory()->create();
    $deck = Deck::factory()->create();
    
    // Enroll user in deck
    $user->enrolledDecks()->attach($deck->id, [
        'enrolled_at' => now(),
        'shortcode' => strtoupper(\Illuminate\Support\Str::random(8)),
    ]);
    
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

test('get recent activity returns most recent reviews from enrolled decks only', function () {
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
    $card4 = Card::factory()->create(['deck_id' => $notEnrolledDeck->id]);

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
    
    CardReview::factory()->create([
        'user_id' => $user->id,
        'card_id' => $card4->id,
        'created_at' => now()->subMinutes(30), // Most recent but not in enrolled deck
    ]);

    $repository = new CardReviewRepository;
    $activity = $repository->getRecentActivity($user->id, 3);

    expect($activity)->toHaveCount(3);
    expect($activity->first()->card_id)->toBe($card1->id); // Most recent from enrolled decks
    expect($activity->pluck('card_id')->toArray())->not->toContain($card4->id);
});
