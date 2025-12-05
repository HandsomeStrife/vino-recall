<?php

declare(strict_types=1);

use Database\Factories\CardFactory;
use Database\Factories\CardReviewFactory;
use Database\Factories\DeckFactory;
use Database\Factories\UserFactory;
use Domain\Card\Actions\ReviewCardAction;
use Domain\Card\Models\CardReview;
use Domain\Deck\Actions\EnrollUserInDeckAction;

test('practice reviews are marked as practice', function () {
    $user = UserFactory::new()->create();
    $deck = DeckFactory::new()->create();

    // Enroll user in deck
    (new EnrollUserInDeckAction())->execute($user->id, $deck->id);

    $card = CardFactory::new()->create([
        'deck_id' => $deck->id,
        'card_type' => 'multiple_choice',
        'answer_choices' => json_encode(['Option A', 'Option B', 'Option C']),
        'correct_answer_indices' => json_encode([0]),
    ]);

    $action = new ReviewCardAction();
    $reviewData = $action->execute($user->id, $card->id, ['Option A'], isPractice: true);

    // Should be marked as practice
    expect($reviewData->is_practice)->toBeTrue();

    // Verify in database
    $practiceReview = CardReview::where('user_id', $user->id)
        ->where('card_id', $card->id)
        ->where('is_practice', true)
        ->first();

    expect($practiceReview)->not->toBeNull();
});

test('practice reviews do not affect SRS scheduling', function () {
    $user = UserFactory::new()->create();
    $deck = DeckFactory::new()->create();

    // Enroll user in deck
    (new EnrollUserInDeckAction())->execute($user->id, $deck->id);

    $card = CardFactory::new()->create([
        'deck_id' => $deck->id,
        'card_type' => 'multiple_choice',
        'answer_choices' => json_encode(['Option A', 'Option B', 'Option C']),
        'correct_answer_indices' => json_encode([0]),
    ]);

    // First, do a real SRS review (correct answer)
    $action = new ReviewCardAction();
    $firstReview = $action->execute($user->id, $card->id, ['Option A'], isPractice: false);

    $originalNextReview = $firstReview->next_review_at;
    $originalEaseFactor = $firstReview->ease_factor;

    // Now do a practice review (incorrect answer)
    // This returns a virtual response without persisting
    $practiceReview = $action->execute($user->id, $card->id, ['Option B'], isPractice: true);

    // Practice review should be marked as practice
    expect($practiceReview->is_practice)->toBeTrue();

    // Get the SRS review record (the only persisted record)
    $srsReview = CardReview::where('user_id', $user->id)
        ->where('card_id', $card->id)
        ->first();

    // SRS schedule should not have changed
    expect($srsReview->next_review_at->toDateTimeString())->toBe($originalNextReview);
    expect((string) $srsReview->ease_factor)->toBe($originalEaseFactor);
    expect($srsReview->is_practice)->toBeFalse();
});

test('SRS reviews update scheduling correctly', function () {
    $user = UserFactory::new()->create();
    $deck = DeckFactory::new()->create();

    // Enroll user in deck
    (new EnrollUserInDeckAction())->execute($user->id, $deck->id);

    $card = CardFactory::new()->create([
        'deck_id' => $deck->id,
        'card_type' => 'multiple_choice',
        'answer_choices' => json_encode(['Option A', 'Option B', 'Option C']),
        'correct_answer_indices' => json_encode([0]),
    ]);

    $action = new ReviewCardAction();

    // First review - correct answer
    $firstReview = $action->execute($user->id, $card->id, ['Option A'], isPractice: false);

    expect($firstReview->is_correct)->toBeTrue();
    expect($firstReview->is_practice)->toBeFalse();
    expect((float) $firstReview->ease_factor)->toBeGreaterThan(2.5); // Should increase

    // Next review should be scheduled for the future
    expect(\Carbon\Carbon::parse($firstReview->next_review_at))->toBeGreaterThan(now());
});

test('incorrect SRS reviews schedule sooner', function () {
    $user = UserFactory::new()->create();
    $deck = DeckFactory::new()->create();

    // Enroll user in deck
    (new EnrollUserInDeckAction())->execute($user->id, $deck->id);

    $card = CardFactory::new()->create([
        'deck_id' => $deck->id,
        'card_type' => 'multiple_choice',
        'answer_choices' => json_encode(['Option A', 'Option B', 'Option C']),
        'correct_answer_indices' => json_encode([0]),
    ]);

    $action = new ReviewCardAction();

    // First review - incorrect answer
    $review = $action->execute($user->id, $card->id, ['Option B'], isPractice: false);

    expect($review->is_correct)->toBeFalse();
    expect($review->is_practice)->toBeFalse();
    expect((float) $review->ease_factor)->toBeLessThan(2.5); // Should decrease

    // Next review should be scheduled for 4 hours from now
    $nextReview = \Carbon\Carbon::parse($review->next_review_at);
    $expectedTime = now()->addHours(4);

    expect($nextReview->diffInMinutes($expectedTime))->toBeLessThan(2); // Allow 2 min tolerance
});

test('practice reviews are excluded from retention rate calculation', function () {
    $user = UserFactory::new()->create();
    $deck = DeckFactory::new()->create();

    // Enroll user in deck
    (new EnrollUserInDeckAction())->execute($user->id, $deck->id);

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

    $action = new ReviewCardAction();

    // Do one correct SRS review on card1
    $action->execute($user->id, $card1->id, ['Option A'], isPractice: false);

    // Do practice reviews on card2 (no SRS review first, so these will be stored as practice)
    // First practice review creates a practice record
    $action->execute($user->id, $card2->id, ['Option B'], isPractice: true);

    $repository = new \Domain\Card\Repositories\CardReviewRepository();
    $retentionRate = $repository->getRetentionRate($user->id, $deck->id);

    // Retention should be 100% (only counting the SRS review, excluding practice)
    expect($retentionRate)->toBe(100.0);
});
