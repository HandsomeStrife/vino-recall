<?php

declare(strict_types=1);

use Carbon\Carbon;
use Domain\Card\Actions\ReviewCardAction;
use Domain\Card\Enums\CardType;
use Domain\Card\Enums\SrsStage;
use Domain\Card\Models\Card;
use Domain\Card\Models\CardReview;
use Domain\Deck\Models\Deck;
use Domain\User\Models\User;

/**
 * Comprehensive SRS Stage and Scheduling Tests
 *
 * These tests verify the WaniKani-style SRS implementation:
 * - Stage promotion on correct answers
 * - Stage demotion on incorrect answers
 * - Correct interval scheduling per stage
 * - Boundary conditions and invariants
 */

// ============================================================================
// HELPER FUNCTIONS
// ============================================================================

function createTestCard(User $user, Deck $deck): Card
{
    return Card::factory()->create([
        'deck_id' => $deck->id,
        'card_type' => CardType::MULTIPLE_CHOICE->value,
        'question' => 'Test question',
        'answer_choices' => json_encode(['Correct', 'Wrong1', 'Wrong2', 'Wrong3']),
        'correct_answer_indices' => json_encode([0]),
    ]);
}

function reviewCard(User $user, Card $card, bool $wasCorrect): \Domain\Card\Data\CardReviewData
{
    $action = new ReviewCardAction;
    $selectedAnswer = $wasCorrect ? ['Correct'] : ['Wrong1'];

    return $action->execute($user->id, $card->id, $selectedAnswer);
}

function setCardStage(User $user, Card $card, int $stage, ?Carbon $nextReviewAt = null): CardReview
{
    $review = CardReview::updateOrCreate(
        ['user_id' => $user->id, 'card_id' => $card->id],
        [
            'srs_stage' => $stage,
            'next_review_at' => $nextReviewAt,
        ]
    );

    return $review;
}

function assertNextReviewWithinTolerance(
    CardReview $review,
    Carbon $expected,
    int $toleranceSeconds = 5
): void {
    if ($expected === null) {
        expect($review->next_review_at)->toBeNull();

        return;
    }

    $actual = $review->next_review_at;
    $diff = abs($expected->diffInSeconds($actual));

    expect($diff)->toBeLessThanOrEqual(
        $toleranceSeconds,
        "Expected next_review_at to be within {$toleranceSeconds}s of {$expected}, got {$actual} (diff: {$diff}s)"
    );
}

// ============================================================================
// 1. PROMOTION ON CORRECT ANSWERS
// ============================================================================

test('1.1 - New card first correct review advances to stage 1 with 4 hour interval', function () {
    Carbon::setTestNow($t0 = now());

    $user = User::factory()->create();
    $deck = Deck::factory()->create();
    $card = createTestCard($user, $deck);

    // Card starts at stage 0 (implicitly - no review exists yet)
    $reviewData = reviewCard($user, $card, wasCorrect: true);

    expect($reviewData->srs_stage)->toBe(1);

    $review = CardReview::where('user_id', $user->id)->where('card_id', $card->id)->first();
    assertNextReviewWithinTolerance($review, $t0->copy()->addHours(4));

    Carbon::setTestNow();
});

test('1.2 - Correct review increments stage 3 to 4 with 47 hour interval', function () {
    Carbon::setTestNow($t0 = now());

    $user = User::factory()->create();
    $deck = Deck::factory()->create();
    $card = createTestCard($user, $deck);

    setCardStage($user, $card, 3, $t0);

    $reviewData = reviewCard($user, $card, wasCorrect: true);

    expect($reviewData->srs_stage)->toBe(4);

    $review = CardReview::where('user_id', $user->id)->where('card_id', $card->id)->first();
    assertNextReviewWithinTolerance($review, $t0->copy()->addHours(47));

    Carbon::setTestNow();
});

test('1.2a - Correct review increments stage 4 to 5 with 7 day interval', function () {
    Carbon::setTestNow($t0 = now());

    $user = User::factory()->create();
    $deck = Deck::factory()->create();
    $card = createTestCard($user, $deck);

    setCardStage($user, $card, 4, $t0);

    $reviewData = reviewCard($user, $card, wasCorrect: true);

    expect($reviewData->srs_stage)->toBe(5);

    $review = CardReview::where('user_id', $user->id)->where('card_id', $card->id)->first();
    assertNextReviewWithinTolerance($review, $t0->copy()->addDays(7));

    Carbon::setTestNow();
});

test('1.2b - Correct review increments stage 5 to 6 with 14 day interval', function () {
    Carbon::setTestNow($t0 = now());

    $user = User::factory()->create();
    $deck = Deck::factory()->create();
    $card = createTestCard($user, $deck);

    setCardStage($user, $card, 5, $t0);

    $reviewData = reviewCard($user, $card, wasCorrect: true);

    expect($reviewData->srs_stage)->toBe(6);

    $review = CardReview::where('user_id', $user->id)->where('card_id', $card->id)->first();
    assertNextReviewWithinTolerance($review, $t0->copy()->addDays(14));

    Carbon::setTestNow();
});

test('1.2c - Correct review increments stage 6 to 7 with 30 day interval', function () {
    Carbon::setTestNow($t0 = now());

    $user = User::factory()->create();
    $deck = Deck::factory()->create();
    $card = createTestCard($user, $deck);

    setCardStage($user, $card, 6, $t0);

    $reviewData = reviewCard($user, $card, wasCorrect: true);

    expect($reviewData->srs_stage)->toBe(7);

    $review = CardReview::where('user_id', $user->id)->where('card_id', $card->id)->first();
    assertNextReviewWithinTolerance($review, $t0->copy()->addDays(30));

    Carbon::setTestNow();
});

test('1.2d - Correct review increments stage 7 to 8 with 120 day interval', function () {
    Carbon::setTestNow($t0 = now());

    $user = User::factory()->create();
    $deck = Deck::factory()->create();
    $card = createTestCard($user, $deck);

    setCardStage($user, $card, 7, $t0);

    $reviewData = reviewCard($user, $card, wasCorrect: true);

    expect($reviewData->srs_stage)->toBe(8);

    $review = CardReview::where('user_id', $user->id)->where('card_id', $card->id)->first();
    assertNextReviewWithinTolerance($review, $t0->copy()->addDays(120));

    Carbon::setTestNow();
});

test('1.2e - Correct review increments stage 8 to 9 (Wine God) with null next_review_at', function () {
    Carbon::setTestNow($t0 = now());

    $user = User::factory()->create();
    $deck = Deck::factory()->create();
    $card = createTestCard($user, $deck);

    setCardStage($user, $card, 8, $t0);

    $reviewData = reviewCard($user, $card, wasCorrect: true);

    expect($reviewData->srs_stage)->toBe(9);
    expect($reviewData->next_review_at)->toBeNull();

    $review = CardReview::where('user_id', $user->id)->where('card_id', $card->id)->first();
    expect($review->srs_stage)->toBe(9);
    expect($review->next_review_at)->toBeNull();

    Carbon::setTestNow();
});

test('1.3 - Stage never exceeds max (9) on correct answer', function () {
    Carbon::setTestNow($t0 = now());

    $user = User::factory()->create();
    $deck = Deck::factory()->create();
    $card = createTestCard($user, $deck);

    setCardStage($user, $card, 9, null);

    $reviewData = reviewCard($user, $card, wasCorrect: true);

    expect($reviewData->srs_stage)->toBe(9);
    expect($reviewData->next_review_at)->toBeNull();

    Carbon::setTestNow();
});

// ============================================================================
// 2. DEMOTION ON INCORRECT ANSWERS
// ============================================================================

test('2.1 - Incorrect in early learning (stage 2) demotes by 1 to stage 1', function () {
    Carbon::setTestNow($t0 = now());

    $user = User::factory()->create();
    $deck = Deck::factory()->create();
    $card = createTestCard($user, $deck);

    setCardStage($user, $card, 2, $t0);

    $reviewData = reviewCard($user, $card, wasCorrect: false);

    expect($reviewData->srs_stage)->toBe(1);

    $review = CardReview::where('user_id', $user->id)->where('card_id', $card->id)->first();
    assertNextReviewWithinTolerance($review, $t0->copy()->addHours(4));

    Carbon::setTestNow();
});

test('2.2 - Incorrect in mid learning (stage 3) demotes by 1 to stage 2', function () {
    Carbon::setTestNow($t0 = now());

    $user = User::factory()->create();
    $deck = Deck::factory()->create();
    $card = createTestCard($user, $deck);

    setCardStage($user, $card, 3, $t0);

    $reviewData = reviewCard($user, $card, wasCorrect: false);

    expect($reviewData->srs_stage)->toBe(2);

    $review = CardReview::where('user_id', $user->id)->where('card_id', $card->id)->first();
    assertNextReviewWithinTolerance($review, $t0->copy()->addHours(8));

    Carbon::setTestNow();
});

test('2.2a - Incorrect in mid learning (stage 4) demotes by 1 to stage 3', function () {
    Carbon::setTestNow($t0 = now());

    $user = User::factory()->create();
    $deck = Deck::factory()->create();
    $card = createTestCard($user, $deck);

    setCardStage($user, $card, 4, $t0);

    $reviewData = reviewCard($user, $card, wasCorrect: false);

    expect($reviewData->srs_stage)->toBe(3);

    $review = CardReview::where('user_id', $user->id)->where('card_id', $card->id)->first();
    assertNextReviewWithinTolerance($review, $t0->copy()->addHours(23));

    Carbon::setTestNow();
});

test('2.3 - Incorrect at high stage (6 Connoisseur II) demotes by 2 to stage 4', function () {
    Carbon::setTestNow($t0 = now());

    $user = User::factory()->create();
    $deck = Deck::factory()->create();
    $card = createTestCard($user, $deck);

    setCardStage($user, $card, 6, $t0);

    $reviewData = reviewCard($user, $card, wasCorrect: false);

    expect($reviewData->srs_stage)->toBe(4);

    $review = CardReview::where('user_id', $user->id)->where('card_id', $card->id)->first();
    assertNextReviewWithinTolerance($review, $t0->copy()->addHours(47));

    Carbon::setTestNow();
});

test('2.4 - Incorrect at Connoisseur I (stage 5) demotes by 2 to stage 3', function () {
    Carbon::setTestNow($t0 = now());

    $user = User::factory()->create();
    $deck = Deck::factory()->create();
    $card = createTestCard($user, $deck);

    setCardStage($user, $card, 5, $t0);

    $reviewData = reviewCard($user, $card, wasCorrect: false);

    expect($reviewData->srs_stage)->toBe(3);

    $review = CardReview::where('user_id', $user->id)->where('card_id', $card->id)->first();
    assertNextReviewWithinTolerance($review, $t0->copy()->addHours(23));

    Carbon::setTestNow();
});

test('2.5 - Incorrect at minimum stage (1) stays at stage 1', function () {
    Carbon::setTestNow($t0 = now());

    $user = User::factory()->create();
    $deck = Deck::factory()->create();
    $card = createTestCard($user, $deck);

    setCardStage($user, $card, 1, $t0);

    $reviewData = reviewCard($user, $card, wasCorrect: false);

    expect($reviewData->srs_stage)->toBe(1);

    $review = CardReview::where('user_id', $user->id)->where('card_id', $card->id)->first();
    assertNextReviewWithinTolerance($review, $t0->copy()->addHours(4));

    Carbon::setTestNow();
});

test('2.6 - Incorrect at Professor (stage 7) demotes by 2 to stage 5', function () {
    Carbon::setTestNow($t0 = now());

    $user = User::factory()->create();
    $deck = Deck::factory()->create();
    $card = createTestCard($user, $deck);

    setCardStage($user, $card, 7, $t0);

    $reviewData = reviewCard($user, $card, wasCorrect: false);

    expect($reviewData->srs_stage)->toBe(5);

    $review = CardReview::where('user_id', $user->id)->where('card_id', $card->id)->first();
    assertNextReviewWithinTolerance($review, $t0->copy()->addDays(7));

    Carbon::setTestNow();
});

test('2.7 - Incorrect at Sommelier (stage 8) demotes by 2 to stage 6', function () {
    Carbon::setTestNow($t0 = now());

    $user = User::factory()->create();
    $deck = Deck::factory()->create();
    $card = createTestCard($user, $deck);

    setCardStage($user, $card, 8, $t0);

    $reviewData = reviewCard($user, $card, wasCorrect: false);

    expect($reviewData->srs_stage)->toBe(6);

    $review = CardReview::where('user_id', $user->id)->where('card_id', $card->id)->first();
    assertNextReviewWithinTolerance($review, $t0->copy()->addDays(14));

    Carbon::setTestNow();
});

test('2.8 - Incorrect at Wine God (stage 9) demotes by 2 to stage 7', function () {
    Carbon::setTestNow($t0 = now());

    $user = User::factory()->create();
    $deck = Deck::factory()->create();
    $card = createTestCard($user, $deck);

    setCardStage($user, $card, 9, null);

    $reviewData = reviewCard($user, $card, wasCorrect: false);

    expect($reviewData->srs_stage)->toBe(7);

    $review = CardReview::where('user_id', $user->id)->where('card_id', $card->id)->first();
    assertNextReviewWithinTolerance($review, $t0->copy()->addDays(30));

    Carbon::setTestNow();
});

// ============================================================================
// 3. SCHEDULING BEHAVIOUR
// ============================================================================

test('3.1 - Next review uses new stage interval, not old', function () {
    Carbon::setTestNow($t0 = now());

    $user = User::factory()->create();
    $deck = Deck::factory()->create();
    $card = createTestCard($user, $deck);

    setCardStage($user, $card, 2, $t0); // Stage 2 has 8 hour interval

    $reviewData = reviewCard($user, $card, wasCorrect: true);

    expect($reviewData->srs_stage)->toBe(3);

    // Should use stage 3 interval (23 hours), NOT stage 2 interval (8 hours)
    $review = CardReview::where('user_id', $user->id)->where('card_id', $card->id)->first();
    assertNextReviewWithinTolerance($review, $t0->copy()->addHours(23));

    Carbon::setTestNow();
});

test('3.2 - Wine God cards have no future reviews', function () {
    Carbon::setTestNow($t0 = now());

    $user = User::factory()->create();
    $deck = Deck::factory()->create();
    $card = createTestCard($user, $deck);

    setCardStage($user, $card, 9, null);

    $reviewData = reviewCard($user, $card, wasCorrect: true);

    expect($reviewData->srs_stage)->toBe(9);
    expect($reviewData->next_review_at)->toBeNull();

    Carbon::setTestNow();
});

test('3.3 - Multiple sequential reviews advance correctly through all stages', function () {
    Carbon::setTestNow($t0 = now());

    $user = User::factory()->create();
    $deck = Deck::factory()->create();
    $card = createTestCard($user, $deck);

    // Review 1: Stage 0 -> 1 (4 hours)
    $reviewData = reviewCard($user, $card, wasCorrect: true);
    expect($reviewData->srs_stage)->toBe(1);

    $review = CardReview::where('user_id', $user->id)->where('card_id', $card->id)->first();
    assertNextReviewWithinTolerance($review, $t0->copy()->addHours(4));

    // Review 2: Stage 1 -> 2 (8 hours)
    Carbon::setTestNow($t0->copy()->addHours(4));
    $reviewData = reviewCard($user, $card, wasCorrect: true);
    expect($reviewData->srs_stage)->toBe(2);

    $review->refresh();
    assertNextReviewWithinTolerance($review, now()->addHours(8));

    // Review 3: Stage 2 -> 3 (23 hours)
    Carbon::setTestNow(now()->addHours(8));
    $reviewData = reviewCard($user, $card, wasCorrect: true);
    expect($reviewData->srs_stage)->toBe(3);

    $review->refresh();
    assertNextReviewWithinTolerance($review, now()->addHours(23));

    // Review 4: Stage 3 -> 4 (47 hours)
    Carbon::setTestNow(now()->addHours(23));
    $reviewData = reviewCard($user, $card, wasCorrect: true);
    expect($reviewData->srs_stage)->toBe(4);

    $review->refresh();
    assertNextReviewWithinTolerance($review, now()->addHours(47));

    // Review 5: Stage 4 -> 5 (7 days)
    Carbon::setTestNow(now()->addHours(47));
    $reviewData = reviewCard($user, $card, wasCorrect: true);
    expect($reviewData->srs_stage)->toBe(5);

    $review->refresh();
    assertNextReviewWithinTolerance($review, now()->addDays(7));

    // Review 6: Stage 5 -> 6 (14 days)
    Carbon::setTestNow(now()->addDays(7));
    $reviewData = reviewCard($user, $card, wasCorrect: true);
    expect($reviewData->srs_stage)->toBe(6);

    $review->refresh();
    assertNextReviewWithinTolerance($review, now()->addDays(14));

    // Review 7: Stage 6 -> 7 (30 days) - Now MASTERED
    Carbon::setTestNow(now()->addDays(14));
    $reviewData = reviewCard($user, $card, wasCorrect: true);
    expect($reviewData->srs_stage)->toBe(7);
    expect(SrsStage::isMastered($reviewData->srs_stage))->toBeTrue();

    $review->refresh();
    assertNextReviewWithinTolerance($review, now()->addDays(30));

    Carbon::setTestNow();
});

// ============================================================================
// 4. BOUNDARY & INVARIANT TESTS
// ============================================================================

test('4.1 - Stage never goes below 0 or above 9 on any action', function () {
    $user = User::factory()->create();
    $deck = Deck::factory()->create();

    // Test all starting stages with both correct and incorrect
    foreach (range(0, 9) as $startingStage) {
        foreach ([true, false] as $wasCorrect) {
            Carbon::setTestNow(now());

            $card = createTestCard($user, $deck);
            if ($startingStage > 0) {
                setCardStage($user, $card, $startingStage, now());
            }

            $reviewData = reviewCard($user, $card, $wasCorrect);

            expect($reviewData->srs_stage)
                ->toBeGreaterThanOrEqual(0)
                ->toBeLessThanOrEqual(9);

            Carbon::setTestNow();
        }
    }
});

test('4.2a - Mastery cannot occur before stage 7 (6 correct reviews)', function () {
    Carbon::setTestNow(now());

    $user = User::factory()->create();
    $deck = Deck::factory()->create();
    $card = createTestCard($user, $deck);

    // Do 6 correct reviews (0 -> 1 -> 2 -> 3 -> 4 -> 5 -> 6)
    for ($i = 0; $i < 6; $i++) {
        reviewCard($user, $card, wasCorrect: true);
    }

    $review = CardReview::where('user_id', $user->id)->where('card_id', $card->id)->first();

    expect($review->srs_stage)->toBe(6);
    expect(SrsStage::isMastered($review->srs_stage))->toBeFalse();

    Carbon::setTestNow();
});

test('4.2b - Mastery occurs at exactly 7 correct reviews from stage 0', function () {
    Carbon::setTestNow(now());

    $user = User::factory()->create();
    $deck = Deck::factory()->create();
    $card = createTestCard($user, $deck);

    // Do 7 correct reviews (0 -> 1 -> 2 -> 3 -> 4 -> 5 -> 6 -> 7)
    for ($i = 0; $i < 7; $i++) {
        reviewCard($user, $card, wasCorrect: true);
    }

    $review = CardReview::where('user_id', $user->id)->where('card_id', $card->id)->first();

    expect($review->srs_stage)->toBe(7);
    expect(SrsStage::isMastered($review->srs_stage))->toBeTrue();

    Carbon::setTestNow();
});

// ============================================================================
// 5. QUEUE / DUE-CARD TESTS
// ============================================================================

test('5.1 - Only cards with next_review_at <= now are returned as due', function () {
    Carbon::setTestNow($t0 = now());

    $user = User::factory()->create();
    $deck = Deck::factory()->create();

    // Enroll user
    $user->enrolledDecks()->attach($deck->id, [
        'enrolled_at' => now(),
        'shortcode' => strtoupper(\Illuminate\Support\Str::random(8)),
    ]);

    $cardA = createTestCard($user, $deck);
    $cardB = createTestCard($user, $deck);

    // Card A: due (next_review_at in the past)
    setCardStage($user, $cardA, 2, $t0->copy()->subMinute());

    // Card B: not due (next_review_at in the future)
    setCardStage($user, $cardB, 3, $t0->copy()->addHour());

    $repository = new \Domain\Card\Repositories\CardReviewRepository;
    $dueCards = $repository->getDueCardsForUser($user->id);

    expect($dueCards->pluck('card_id')->toArray())->toContain($cardA->id);
    expect($dueCards->pluck('card_id')->toArray())->not->toContain($cardB->id);

    Carbon::setTestNow();
});

test('5.2 - Wine God cards (stage 9) are never in the due queue', function () {
    Carbon::setTestNow($t0 = now());

    $user = User::factory()->create();
    $deck = Deck::factory()->create();

    // Enroll user
    $user->enrolledDecks()->attach($deck->id, [
        'enrolled_at' => now(),
        'shortcode' => strtoupper(\Illuminate\Support\Str::random(8)),
    ]);

    $card = createTestCard($user, $deck);

    // Set card to Wine God with null next_review_at
    setCardStage($user, $card, 9, null);

    $repository = new \Domain\Card\Repositories\CardReviewRepository;
    $dueCards = $repository->getDueCardsForUser($user->id);

    expect($dueCards->pluck('card_id')->toArray())->not->toContain($card->id);

    Carbon::setTestNow();
});

test('5.3 - Uncorked cards (stage 0) are not in review queue until reviewed', function () {
    Carbon::setTestNow($t0 = now());

    $user = User::factory()->create();
    $deck = Deck::factory()->create();

    // Enroll user
    $user->enrolledDecks()->attach($deck->id, [
        'enrolled_at' => now(),
        'shortcode' => strtoupper(\Illuminate\Support\Str::random(8)),
    ]);

    // Create card but don't review it (stays at stage 0, not in card_reviews)
    $card = createTestCard($user, $deck);

    $repository = new \Domain\Card\Repositories\CardReviewRepository;
    $dueCards = $repository->getDueCardsForUser($user->id);

    // Card not in due queue because it has no CardReview record yet
    expect($dueCards->pluck('card_id')->toArray())->not->toContain($card->id);

    Carbon::setTestNow();
});

// ============================================================================
// 6. INTERVAL VERIFICATION FOR ALL STAGES
// ============================================================================

test('6.1 - Stage 1 (Hobbyist I) interval is exactly 4 hours', function () {
    $interval = SrsStage::intervalForStage(1);
    expect($interval)->not->toBeNull();

    $now = Carbon::now();
    $expected = $now->copy()->addHours(4);

    expect($now->add($interval)->equalTo($expected))->toBeTrue();
});

test('6.2 - Stage 2 (Hobbyist II) interval is exactly 8 hours', function () {
    $interval = SrsStage::intervalForStage(2);
    expect($interval)->not->toBeNull();

    $now = Carbon::now();
    $expected = $now->copy()->addHours(8);

    expect($now->add($interval)->equalTo($expected))->toBeTrue();
});

test('6.3 - Stage 3 (Student I) interval is exactly 23 hours', function () {
    $interval = SrsStage::intervalForStage(3);
    expect($interval)->not->toBeNull();

    $now = Carbon::now();
    $expected = $now->copy()->addHours(23);

    expect($now->add($interval)->equalTo($expected))->toBeTrue();
});

test('6.4 - Stage 4 (Student II) interval is exactly 47 hours', function () {
    $interval = SrsStage::intervalForStage(4);
    expect($interval)->not->toBeNull();

    $now = Carbon::now();
    $expected = $now->copy()->addHours(47);

    expect($now->add($interval)->equalTo($expected))->toBeTrue();
});

test('6.5 - Stage 5 (Connoisseur I) interval is exactly 7 days', function () {
    $interval = SrsStage::intervalForStage(5);
    expect($interval)->not->toBeNull();

    $now = Carbon::now();
    $expected = $now->copy()->addDays(7);

    expect($now->add($interval)->equalTo($expected))->toBeTrue();
});

test('6.6 - Stage 6 (Connoisseur II) interval is exactly 14 days', function () {
    $interval = SrsStage::intervalForStage(6);
    expect($interval)->not->toBeNull();

    $now = Carbon::now();
    $expected = $now->copy()->addDays(14);

    expect($now->add($interval)->equalTo($expected))->toBeTrue();
});

test('6.7 - Stage 7 (Professor) interval is exactly 30 days', function () {
    $interval = SrsStage::intervalForStage(7);
    expect($interval)->not->toBeNull();

    $now = Carbon::now();
    $expected = $now->copy()->addDays(30);

    expect($now->add($interval)->equalTo($expected))->toBeTrue();
});

test('6.8 - Stage 8 (Sommelier) interval is exactly 120 days', function () {
    $interval = SrsStage::intervalForStage(8);
    expect($interval)->not->toBeNull();

    $now = Carbon::now();
    $expected = $now->copy()->addDays(120);

    expect($now->add($interval)->equalTo($expected))->toBeTrue();
});

test('6.9 - Stage 9 (Wine God) has null interval (retired)', function () {
    $interval = SrsStage::intervalForStage(9);
    expect($interval)->toBeNull();
});

test('6.10 - Stage 0 (Uncorked) has null interval (not yet reviewed)', function () {
    $interval = SrsStage::intervalForStage(0);
    expect($interval)->toBeNull();
});

// ============================================================================
// 7. SRS ENUM HELPER METHODS
// ============================================================================

test('7.1 - calculateNewStageOnCorrect returns min(stage+1, 9)', function () {
    expect(SrsStage::calculateNewStageOnCorrect(0))->toBe(1);
    expect(SrsStage::calculateNewStageOnCorrect(4))->toBe(5);
    expect(SrsStage::calculateNewStageOnCorrect(8))->toBe(9);
    expect(SrsStage::calculateNewStageOnCorrect(9))->toBe(9); // Max
});

test('7.2 - calculateNewStageOnIncorrect follows spec rules', function () {
    // Early learning (stage 0-1): stays at 1
    expect(SrsStage::calculateNewStageOnIncorrect(0))->toBe(1);
    expect(SrsStage::calculateNewStageOnIncorrect(1))->toBe(1);

    // Mid learning (stage 2-4): subtract 1
    expect(SrsStage::calculateNewStageOnIncorrect(2))->toBe(1);
    expect(SrsStage::calculateNewStageOnIncorrect(3))->toBe(2);
    expect(SrsStage::calculateNewStageOnIncorrect(4))->toBe(3);

    // High stages (stage 5+): subtract 2, min 1
    expect(SrsStage::calculateNewStageOnIncorrect(5))->toBe(3);
    expect(SrsStage::calculateNewStageOnIncorrect(6))->toBe(4);
    expect(SrsStage::calculateNewStageOnIncorrect(7))->toBe(5);
    expect(SrsStage::calculateNewStageOnIncorrect(8))->toBe(6);
    expect(SrsStage::calculateNewStageOnIncorrect(9))->toBe(7);
});

test('7.3 - isMastered returns true only for stage >= 7', function () {
    expect(SrsStage::isMastered(0))->toBeFalse();
    expect(SrsStage::isMastered(1))->toBeFalse();
    expect(SrsStage::isMastered(5))->toBeFalse();
    expect(SrsStage::isMastered(6))->toBeFalse();
    expect(SrsStage::isMastered(7))->toBeTrue();
    expect(SrsStage::isMastered(8))->toBeTrue();
    expect(SrsStage::isMastered(9))->toBeTrue();
});

test('7.4 - SrsStage enum has correct stage names', function () {
    expect(SrsStage::UNCORKED->getName())->toBe('Uncorked');
    expect(SrsStage::HOBBYIST_I->getName())->toBe('Hobbyist I');
    expect(SrsStage::HOBBYIST_II->getName())->toBe('Hobbyist II');
    expect(SrsStage::STUDENT_I->getName())->toBe('Student I');
    expect(SrsStage::STUDENT_II->getName())->toBe('Student II');
    expect(SrsStage::CONNOISSEUR_I->getName())->toBe('Connoisseur I');
    expect(SrsStage::CONNOISSEUR_II->getName())->toBe('Connoisseur II');
    expect(SrsStage::PROFESSOR->getName())->toBe('Professor');
    expect(SrsStage::SOMMELIER->getName())->toBe('Sommelier');
    expect(SrsStage::WINE_GOD->getName())->toBe('Wine God');
});
