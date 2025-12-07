<?php

declare(strict_types=1);

use Domain\Card\Enums\SrsStage;
use Domain\Card\Models\Card;
use Domain\Card\Models\CardReview;
use Domain\Card\Models\ReviewHistory;
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
        'srs_stage' => 3,
        'next_review_at' => now()->subDay(),
    ]);

    CardReview::factory()->create([
        'user_id' => $user->id,
        'card_id' => $card2->id,
        'srs_stage' => 2,
        'next_review_at' => now()->addDay(),
    ]);

    CardReview::factory()->create([
        'user_id' => $user->id,
        'card_id' => $card3->id,
        'srs_stage' => 3,
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

    // Card1: mastered (srs_stage >= MASTERED_THRESHOLD)
    CardReview::factory()->create([
        'user_id' => $user->id,
        'card_id' => $card1->id,
        'srs_stage' => SrsStage::MASTERED_THRESHOLD, // 7
    ]);

    // Card2: not mastered (srs_stage < MASTERED_THRESHOLD)
    CardReview::factory()->create([
        'user_id' => $user->id,
        'card_id' => $card2->id,
        'srs_stage' => 3,
    ]);

    // Card3: mastered (srs_stage >= MASTERED_THRESHOLD)
    CardReview::factory()->create([
        'user_id' => $user->id,
        'card_id' => $card3->id,
        'srs_stage' => 8,
    ]);

    // Card4: mastered but not in enrolled deck
    CardReview::factory()->create([
        'user_id' => $user->id,
        'card_id' => $card4->id,
        'srs_stage' => SrsStage::MASTERED_THRESHOLD,
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

    // Review history entries for streak calculation
    ReviewHistory::factory()->create([
        'user_id' => $user->id,
        'card_id' => $card1->id,
        'is_practice' => false,
        'reviewed_at' => now(),
    ]);

    ReviewHistory::factory()->create([
        'user_id' => $user->id,
        'card_id' => $card2->id,
        'is_practice' => false,
        'reviewed_at' => now()->subDay(),
    ]);

    ReviewHistory::factory()->create([
        'user_id' => $user->id,
        'card_id' => $card3->id,
        'is_practice' => false,
        'reviewed_at' => now()->subDays(2),
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

    ReviewHistory::factory()->create([
        'user_id' => $user->id,
        'card_id' => $card1->id,
        'reviewed_at' => now()->subHours(1),
    ]);

    ReviewHistory::factory()->create([
        'user_id' => $user->id,
        'card_id' => $card2->id,
        'reviewed_at' => now()->subHours(2),
    ]);

    ReviewHistory::factory()->create([
        'user_id' => $user->id,
        'card_id' => $card3->id,
        'reviewed_at' => now()->subHours(3),
    ]);

    ReviewHistory::factory()->create([
        'user_id' => $user->id,
        'card_id' => $card4->id,
        'reviewed_at' => now()->subMinutes(30), // Most recent but not in enrolled deck
    ]);

    $repository = new CardReviewRepository;
    $activity = $repository->getRecentActivity($user->id, 3);

    expect($activity)->toHaveCount(3);
    expect($activity->first()->card_id)->toBe($card1->id); // Most recent from enrolled decks
    expect($activity->pluck('card_id')->toArray())->not->toContain($card4->id);
});

test('get accuracy returns correct percentage from review history', function () {
    $user = User::factory()->create();
    $deck = Deck::factory()->create();

    $card1 = Card::factory()->create(['deck_id' => $deck->id]);
    $card2 = Card::factory()->create(['deck_id' => $deck->id]);

    // 3 correct, 1 incorrect = 75% accuracy
    ReviewHistory::factory()->create([
        'user_id' => $user->id,
        'card_id' => $card1->id,
        'is_correct' => true,
        'is_practice' => false,
    ]);

    ReviewHistory::factory()->create([
        'user_id' => $user->id,
        'card_id' => $card1->id,
        'is_correct' => true,
        'is_practice' => false,
    ]);

    ReviewHistory::factory()->create([
        'user_id' => $user->id,
        'card_id' => $card2->id,
        'is_correct' => true,
        'is_practice' => false,
    ]);

    ReviewHistory::factory()->create([
        'user_id' => $user->id,
        'card_id' => $card2->id,
        'is_correct' => false,
        'is_practice' => false,
    ]);

    $repository = new CardReviewRepository;
    $accuracy = $repository->getAccuracy($user->id, $deck->id);

    expect($accuracy)->toBe(75.0);
});

test('get accuracy excludes practice reviews', function () {
    $user = User::factory()->create();
    $deck = Deck::factory()->create();

    $card = Card::factory()->create(['deck_id' => $deck->id]);

    // 1 correct SRS review
    ReviewHistory::factory()->create([
        'user_id' => $user->id,
        'card_id' => $card->id,
        'is_correct' => true,
        'is_practice' => false,
    ]);

    // 2 incorrect practice reviews (should be excluded)
    ReviewHistory::factory()->create([
        'user_id' => $user->id,
        'card_id' => $card->id,
        'is_correct' => false,
        'is_practice' => true,
    ]);

    ReviewHistory::factory()->create([
        'user_id' => $user->id,
        'card_id' => $card->id,
        'is_correct' => false,
        'is_practice' => true,
    ]);

    $repository = new CardReviewRepository;
    $accuracy = $repository->getAccuracy($user->id, $deck->id);

    expect($accuracy)->toBe(100.0); // Only the SRS review counts
});

test('get progress returns correct stage-based percentage', function () {
    $user = User::factory()->create();
    $deck = Deck::factory()->create();

    // 4 cards: stage 0, 3, 6, 9
    // Progress = (0 + 3 + 6 + 9) / 9 / 4 * 100 = 18/9/4 * 100 = 50%
    $card1 = Card::factory()->create(['deck_id' => $deck->id]);
    $card2 = Card::factory()->create(['deck_id' => $deck->id]);
    $card3 = Card::factory()->create(['deck_id' => $deck->id]);
    $card4 = Card::factory()->create(['deck_id' => $deck->id]);

    // card1 not in card_reviews (stage 0)

    CardReview::factory()->create([
        'user_id' => $user->id,
        'card_id' => $card2->id,
        'srs_stage' => 3,
    ]);

    CardReview::factory()->create([
        'user_id' => $user->id,
        'card_id' => $card3->id,
        'srs_stage' => 6,
    ]);

    CardReview::factory()->create([
        'user_id' => $user->id,
        'card_id' => $card4->id,
        'srs_stage' => 9,
    ]);

    $repository = new CardReviewRepository;
    $progress = $repository->getProgress($user->id, $deck->id, 4);

    expect($progress)->toBe(50.0);
});
