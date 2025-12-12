<?php

declare(strict_types=1);

use Database\Factories\CardFactory;
use Database\Factories\DeckFactory;
use Database\Factories\UserFactory;
use Domain\Card\Actions\ReviewCardAction;
use Domain\Card\Models\CardReview;
use Domain\Card\Models\ReviewHistory;
use Domain\Deck\Actions\EnrollUserInDeckAction;

test('practice reviews are logged to review history with is_practice flag', function () {
    $user = UserFactory::new()->create();
    $deck = DeckFactory::new()->create();

    // Enroll user in deck
    (new EnrollUserInDeckAction)->execute($user->id, $deck->id);

    $card = CardFactory::new()->create([
        'deck_id' => $deck->id,
        'card_type' => 'multiple_choice',
        'answer_choices' => json_encode(['Option A', 'Option B', 'Option C']),
        'correct_answer_indices' => json_encode([0]),
    ]);

    $action = new ReviewCardAction;
    $action->execute($user->id, $card->id, ['Option A'], isPractice: true);

    // Check review history
    $history = ReviewHistory::where('user_id', $user->id)
        ->where('card_id', $card->id)
        ->first();

    expect($history)->not->toBeNull();
    expect($history->is_practice)->toBeTrue();
    expect($history->is_correct)->toBeTrue();
});

test('practice reviews do not advance SRS stage', function () {
    $user = UserFactory::new()->create();
    $deck = DeckFactory::new()->create();

    // Enroll user in deck
    (new EnrollUserInDeckAction)->execute($user->id, $deck->id);

    $card = CardFactory::new()->create([
        'deck_id' => $deck->id,
        'card_type' => 'multiple_choice',
        'answer_choices' => json_encode(['Option A', 'Option B', 'Option C']),
        'correct_answer_indices' => json_encode([0]),
    ]);

    // First, do a real SRS review (correct answer) - advances to stage 1
    $action = new ReviewCardAction;
    $firstReview = $action->execute($user->id, $card->id, ['Option A'], isPractice: false);

    expect($firstReview->srs_stage)->toBe(1);
    $originalNextReview = $firstReview->next_review_at;

    // Now do a practice review (correct answer)
    // This should NOT advance the stage
    $action->execute($user->id, $card->id, ['Option A'], isPractice: true);

    // Get the SRS review record
    $srsReview = CardReview::where('user_id', $user->id)
        ->where('card_id', $card->id)
        ->first();

    // SRS stage should not have changed
    expect($srsReview->srs_stage)->toBe(1);
    expect($srsReview->next_review_at->toDateTimeString())->toBe($originalNextReview);
});

test('practice reviews do not demote SRS stage on incorrect answer', function () {
    $user = UserFactory::new()->create();
    $deck = DeckFactory::new()->create();

    // Enroll user in deck
    (new EnrollUserInDeckAction)->execute($user->id, $deck->id);

    $card = CardFactory::new()->create([
        'deck_id' => $deck->id,
        'card_type' => 'multiple_choice',
        'answer_choices' => json_encode(['Option A', 'Option B', 'Option C']),
        'correct_answer_indices' => json_encode([0]),
    ]);

    // Create a card at stage 3
    CardReview::create([
        'user_id' => $user->id,
        'card_id' => $card->id,
        'srs_stage' => 3,
        'next_review_at' => now()->addDay(),
    ]);

    // Do a practice review with incorrect answer
    $action = new ReviewCardAction;
    $action->execute($user->id, $card->id, ['Option B'], isPractice: true);

    // Stage should not have changed
    $review = CardReview::where('user_id', $user->id)->where('card_id', $card->id)->first();
    expect($review->srs_stage)->toBe(3);
});

test('SRS reviews advance stage correctly', function () {
    $user = UserFactory::new()->create();
    $deck = DeckFactory::new()->create();

    // Enroll user in deck
    (new EnrollUserInDeckAction)->execute($user->id, $deck->id);

    $card = CardFactory::new()->create([
        'deck_id' => $deck->id,
        'card_type' => 'multiple_choice',
        'answer_choices' => json_encode(['Option A', 'Option B', 'Option C']),
        'correct_answer_indices' => json_encode([0]),
    ]);

    $action = new ReviewCardAction;

    // First review - correct answer (stage 0 -> 1)
    $firstReview = $action->execute($user->id, $card->id, ['Option A'], isPractice: false);

    expect($firstReview->srs_stage)->toBe(1);

    // Next review should be scheduled for the future (~4 hours for stage 1)
    expect(\Carbon\Carbon::parse($firstReview->next_review_at))->toBeGreaterThan(now());

    // History should show correct answer
    $history = ReviewHistory::where('user_id', $user->id)
        ->where('card_id', $card->id)
        ->first();
    expect($history->is_correct)->toBeTrue();
    expect($history->is_practice)->toBeFalse();
});

test('incorrect SRS reviews demote stage and schedule sooner', function () {
    $user = UserFactory::new()->create();
    $deck = DeckFactory::new()->create();

    // Enroll user in deck
    (new EnrollUserInDeckAction)->execute($user->id, $deck->id);

    $card = CardFactory::new()->create([
        'deck_id' => $deck->id,
        'card_type' => 'multiple_choice',
        'answer_choices' => json_encode(['Option A', 'Option B', 'Option C']),
        'correct_answer_indices' => json_encode([0]),
    ]);

    $action = new ReviewCardAction;

    // First review - incorrect answer (stage 0 -> 1, but still early stage)
    $review = $action->execute($user->id, $card->id, ['Option B'], isPractice: false);

    expect($review->srs_stage)->toBe(1); // Still goes to 1 from 0
    expect(\Carbon\Carbon::parse($review->next_review_at))->toBeGreaterThan(now());

    // History should show incorrect answer
    $history = ReviewHistory::where('user_id', $user->id)
        ->where('card_id', $card->id)
        ->first();
    expect($history->is_correct)->toBeFalse();
});

test('practice reviews are excluded from accuracy calculation', function () {
    $user = UserFactory::new()->create();
    $deck = DeckFactory::new()->create();

    // Enroll user in deck
    (new EnrollUserInDeckAction)->execute($user->id, $deck->id);

    $card1 = CardFactory::new()->create([
        'deck_id' => $deck->id,
        'card_type' => 'multiple_choice',
        'answer_choices' => json_encode(['Option A', 'Option B', 'Option C']),
        'correct_answer_indices' => json_encode([0]),
    ]);

    $card2 = CardFactory::new()->create([
        'deck_id' => $deck->id,
        'card_type' => 'multiple_choice',
        'answer_choices' => json_encode(['Option A', 'Option B', 'Option C']),
        'correct_answer_indices' => json_encode([0]),
    ]);

    $action = new ReviewCardAction;

    // Do one correct SRS review on card1
    $action->execute($user->id, $card1->id, ['Option A'], isPractice: false);

    // Do practice reviews on card2 (incorrect)
    $action->execute($user->id, $card2->id, ['Option B'], isPractice: true);

    $repository = new \Domain\Card\Repositories\CardReviewRepository;
    $accuracy = $repository->getAccuracy($user->id, $deck->id);

    // Accuracy should be 100% (only counting the SRS review, excluding practice)
    expect($accuracy)->toBe(100.0);
});
