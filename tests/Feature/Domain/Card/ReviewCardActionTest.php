<?php

declare(strict_types=1);

use Domain\Card\Actions\ReviewCardAction;
use Domain\Card\Enums\CardType;
use Domain\Card\Enums\SrsStage;
use Domain\Card\Models\Card;
use Domain\Card\Models\CardReview;
use Domain\Card\Models\ReviewHistory;
use Domain\Deck\Models\Deck;
use Domain\User\Models\User;

test('review card action creates new review with stage 1 for first correct answer', function () {
    $user = User::factory()->create();
    $deck = Deck::factory()->create();
    $card = Card::factory()->create([
        'deck_id' => $deck->id,
        'card_type' => CardType::MULTIPLE_CHOICE->value,
        'question' => 'What is the capital of France?',
        'answer_choices' => json_encode(['Paris', 'London', 'Berlin', 'Madrid']),
        'correct_answer_indices' => json_encode([0]),
    ]);
    $action = new ReviewCardAction;

    $reviewData = $action->execute($user->id, $card->id, ['Paris']);

    expect($reviewData->user_id)->toBe($user->id);
    expect($reviewData->card_id)->toBe($card->id);
    expect($reviewData->srs_stage)->toBe(1); // Stage advances from 0 to 1

    $review = CardReview::where('user_id', $user->id)
        ->where('card_id', $card->id)
        ->first();

    expect($review)->not->toBeNull();
    expect($review->srs_stage)->toBe(1);
});

test('review card action creates new review with stage 1 for first incorrect answer', function () {
    $user = User::factory()->create();
    $deck = Deck::factory()->create();
    $card = Card::factory()->create([
        'deck_id' => $deck->id,
        'card_type' => CardType::MULTIPLE_CHOICE->value,
        'question' => 'What is the capital of France?',
        'answer_choices' => json_encode(['Paris', 'London', 'Berlin', 'Madrid']),
        'correct_answer_indices' => json_encode([0]),
    ]);
    $action = new ReviewCardAction;

    $reviewData = $action->execute($user->id, $card->id, ['London']);

    expect($reviewData->srs_stage)->toBe(1); // Stage 0 incorrect goes to 1
});

test('review card action logs correct answer to review history', function () {
    $user = User::factory()->create();
    $deck = Deck::factory()->create();
    $card = Card::factory()->create([
        'deck_id' => $deck->id,
        'card_type' => CardType::MULTIPLE_CHOICE->value,
        'question' => 'What is the capital of France?',
        'answer_choices' => json_encode(['Paris', 'London', 'Berlin', 'Madrid']),
        'correct_answer_indices' => json_encode([0]),
    ]);
    $action = new ReviewCardAction;

    $action->execute($user->id, $card->id, ['Paris']);

    $history = ReviewHistory::where('user_id', $user->id)
        ->where('card_id', $card->id)
        ->first();

    expect($history)->not->toBeNull();
    expect($history->is_correct)->toBeTrue();
    expect($history->previous_stage)->toBe(0);
    expect($history->new_stage)->toBe(1);
    expect($history->is_practice)->toBeFalse();
});

test('review card action logs incorrect answer to review history', function () {
    $user = User::factory()->create();
    $deck = Deck::factory()->create();
    $card = Card::factory()->create([
        'deck_id' => $deck->id,
        'card_type' => CardType::MULTIPLE_CHOICE->value,
        'question' => 'What is the capital of France?',
        'answer_choices' => json_encode(['Paris', 'London', 'Berlin', 'Madrid']),
        'correct_answer_indices' => json_encode([0]),
    ]);
    $action = new ReviewCardAction;

    $action->execute($user->id, $card->id, ['London']);

    $history = ReviewHistory::where('user_id', $user->id)
        ->where('card_id', $card->id)
        ->first();

    expect($history)->not->toBeNull();
    expect($history->is_correct)->toBeFalse();
});

test('review card action advances stage on consecutive correct answers', function () {
    $user = User::factory()->create();
    $deck = Deck::factory()->create();
    $card = Card::factory()->create([
        'deck_id' => $deck->id,
        'card_type' => CardType::MULTIPLE_CHOICE->value,
        'question' => 'What is the capital of France?',
        'answer_choices' => json_encode(['Paris', 'London', 'Berlin', 'Madrid']),
        'correct_answer_indices' => json_encode([0]),
    ]);
    $action = new ReviewCardAction;

    // First correct answer: 0 -> 1
    $action->execute($user->id, $card->id, ['Paris']);
    expect(CardReview::where('user_id', $user->id)->where('card_id', $card->id)->first()->srs_stage)->toBe(1);

    // Second correct answer: 1 -> 2
    $action->execute($user->id, $card->id, ['Paris']);
    expect(CardReview::where('user_id', $user->id)->where('card_id', $card->id)->first()->srs_stage)->toBe(2);

    // Third correct answer: 2 -> 3
    $action->execute($user->id, $card->id, ['Paris']);
    expect(CardReview::where('user_id', $user->id)->where('card_id', $card->id)->first()->srs_stage)->toBe(3);
});

test('review card action demotes stage on incorrect answer for mid-level card', function () {
    $user = User::factory()->create();
    $deck = Deck::factory()->create();
    $card = Card::factory()->create([
        'deck_id' => $deck->id,
        'card_type' => CardType::MULTIPLE_CHOICE->value,
        'question' => 'What is the capital of France?',
        'answer_choices' => json_encode(['Paris', 'London', 'Berlin', 'Madrid']),
        'correct_answer_indices' => json_encode([0]),
    ]);

    // Create existing review at stage 3
    CardReview::create([
        'user_id' => $user->id,
        'card_id' => $card->id,
        'srs_stage' => 3,
        'next_review_at' => now(),
    ]);

    $action = new ReviewCardAction;
    $action->execute($user->id, $card->id, ['London']); // Incorrect

    // Stage 3 incorrect -> stage 2 (mid-level: -1)
    expect(CardReview::where('user_id', $user->id)->where('card_id', $card->id)->first()->srs_stage)->toBe(2);
});

test('review card action demotes stage by 2 for high-level card on incorrect', function () {
    $user = User::factory()->create();
    $deck = Deck::factory()->create();
    $card = Card::factory()->create([
        'deck_id' => $deck->id,
        'card_type' => CardType::MULTIPLE_CHOICE->value,
        'question' => 'What is the capital of France?',
        'answer_choices' => json_encode(['Paris', 'London', 'Berlin', 'Madrid']),
        'correct_answer_indices' => json_encode([0]),
    ]);

    // Create existing review at stage 6 (Connoisseur II)
    CardReview::create([
        'user_id' => $user->id,
        'card_id' => $card->id,
        'srs_stage' => 6,
        'next_review_at' => now(),
    ]);

    $action = new ReviewCardAction;
    $action->execute($user->id, $card->id, ['London']); // Incorrect

    // Stage 6 incorrect -> stage 4 (high-level: -2)
    expect(CardReview::where('user_id', $user->id)->where('card_id', $card->id)->first()->srs_stage)->toBe(4);
});

test('review card action sets correct next_review_at for stage 1 (4 hours)', function () {
    $user = User::factory()->create();
    $deck = Deck::factory()->create();
    $card = Card::factory()->create([
        'deck_id' => $deck->id,
        'card_type' => CardType::MULTIPLE_CHOICE->value,
        'question' => 'What is the capital of France?',
        'answer_choices' => json_encode(['Paris', 'London', 'Berlin', 'Madrid']),
        'correct_answer_indices' => json_encode([0]),
    ]);
    $action = new ReviewCardAction;

    $action->execute($user->id, $card->id, ['Paris']); // Correct, advances to stage 1

    $review = CardReview::where('user_id', $user->id)->where('card_id', $card->id)->first();
    $hoursUntilReview = now()->diffInHours($review->next_review_at, false);
    expect($hoursUntilReview)->toBeLessThanOrEqual(4);
    expect($hoursUntilReview)->toBeGreaterThanOrEqual(3);
});

test('review card action sets null next_review_at for stage 9 (Wine God)', function () {
    $user = User::factory()->create();
    $deck = Deck::factory()->create();
    $card = Card::factory()->create([
        'deck_id' => $deck->id,
        'card_type' => CardType::MULTIPLE_CHOICE->value,
        'question' => 'What is the capital of France?',
        'answer_choices' => json_encode(['Paris', 'London', 'Berlin', 'Madrid']),
        'correct_answer_indices' => json_encode([0]),
    ]);

    // Create existing review at stage 8 (Sommelier)
    CardReview::create([
        'user_id' => $user->id,
        'card_id' => $card->id,
        'srs_stage' => 8,
        'next_review_at' => now(),
    ]);

    $action = new ReviewCardAction;
    $action->execute($user->id, $card->id, ['Paris']); // Correct, advances to stage 9

    $review = CardReview::where('user_id', $user->id)->where('card_id', $card->id)->first();
    expect($review->srs_stage)->toBe(9);
    expect($review->next_review_at)->toBeNull(); // Wine God has no more reviews
});

test('practice mode logs to history but does not advance SRS stage', function () {
    $user = User::factory()->create();
    $deck = Deck::factory()->create();
    $card = Card::factory()->create([
        'deck_id' => $deck->id,
        'card_type' => CardType::MULTIPLE_CHOICE->value,
        'question' => 'What is the capital of France?',
        'answer_choices' => json_encode(['Paris', 'London', 'Berlin', 'Madrid']),
        'correct_answer_indices' => json_encode([0]),
    ]);

    // Create existing review at stage 3
    CardReview::create([
        'user_id' => $user->id,
        'card_id' => $card->id,
        'srs_stage' => 3,
        'next_review_at' => now(),
    ]);

    $action = new ReviewCardAction;
    $action->execute($user->id, $card->id, ['Paris'], isPractice: true);

    // Stage should remain unchanged
    $review = CardReview::where('user_id', $user->id)->where('card_id', $card->id)->first();
    expect($review->srs_stage)->toBe(3);

    // But history should be logged with is_practice flag
    $history = ReviewHistory::where('user_id', $user->id)
        ->where('card_id', $card->id)
        ->latest()
        ->first();
    expect($history->is_practice)->toBeTrue();
    expect($history->is_correct)->toBeTrue();
    expect($history->new_stage)->toBe(3); // Practice doesn't change stage in history
});

// Multi-answer tests

test('review card action marks correct when all correct answers selected', function () {
    $user = User::factory()->create();
    $deck = Deck::factory()->create();
    $card = Card::factory()->create([
        'deck_id' => $deck->id,
        'card_type' => CardType::MULTIPLE_CHOICE->value,
        'question' => 'Which are red grape varieties?',
        'answer_choices' => json_encode(['Chardonnay', 'Merlot', 'Riesling', 'Syrah']),
        'correct_answer_indices' => json_encode([1, 3]), // Merlot and Syrah
    ]);
    $action = new ReviewCardAction;

    $reviewData = $action->execute($user->id, $card->id, ['Merlot', 'Syrah']);

    expect($reviewData->srs_stage)->toBe(1); // Advanced from 0 to 1

    $history = ReviewHistory::where('user_id', $user->id)->where('card_id', $card->id)->first();
    expect($history->is_correct)->toBeTrue();
});

test('review card action marks incorrect when only partial correct answers selected', function () {
    $user = User::factory()->create();
    $deck = Deck::factory()->create();
    $card = Card::factory()->create([
        'deck_id' => $deck->id,
        'card_type' => CardType::MULTIPLE_CHOICE->value,
        'question' => 'Which are red grape varieties?',
        'answer_choices' => json_encode(['Chardonnay', 'Merlot', 'Riesling', 'Syrah']),
        'correct_answer_indices' => json_encode([1, 3]), // Merlot and Syrah
    ]);
    $action = new ReviewCardAction;

    // Only select Merlot, missing Syrah
    $reviewData = $action->execute($user->id, $card->id, ['Merlot']);

    $history = ReviewHistory::where('user_id', $user->id)->where('card_id', $card->id)->first();
    expect($history->is_correct)->toBeFalse();
});

test('review card action marks incorrect when extra incorrect answer selected', function () {
    $user = User::factory()->create();
    $deck = Deck::factory()->create();
    $card = Card::factory()->create([
        'deck_id' => $deck->id,
        'card_type' => CardType::MULTIPLE_CHOICE->value,
        'question' => 'Which are red grape varieties?',
        'answer_choices' => json_encode(['Chardonnay', 'Merlot', 'Riesling', 'Syrah']),
        'correct_answer_indices' => json_encode([1, 3]), // Merlot and Syrah
    ]);
    $action = new ReviewCardAction;

    // Select correct answers plus an incorrect one
    $action->execute($user->id, $card->id, ['Merlot', 'Syrah', 'Chardonnay']);

    $history = ReviewHistory::where('user_id', $user->id)->where('card_id', $card->id)->first();
    expect($history->is_correct)->toBeFalse();
});

test('review card action marks correct regardless of answer order', function () {
    $user = User::factory()->create();
    $deck = Deck::factory()->create();
    $card = Card::factory()->create([
        'deck_id' => $deck->id,
        'card_type' => CardType::MULTIPLE_CHOICE->value,
        'question' => 'Which are red grape varieties?',
        'answer_choices' => json_encode(['Chardonnay', 'Merlot', 'Riesling', 'Syrah']),
        'correct_answer_indices' => json_encode([1, 3]), // Merlot and Syrah
    ]);
    $action = new ReviewCardAction;

    // Select in reverse order
    $action->execute($user->id, $card->id, ['Syrah', 'Merlot']);

    $history = ReviewHistory::where('user_id', $user->id)->where('card_id', $card->id)->first();
    expect($history->is_correct)->toBeTrue();
});

// Acceptance criteria tests from spec

test('Scenario A: New deck first perfect session', function () {
    $user = User::factory()->create();
    $deck = Deck::factory()->create();

    // Create 10 cards
    $cards = [];
    for ($i = 0; $i < 10; $i++) {
        $cards[] = Card::factory()->create([
            'deck_id' => $deck->id,
            'card_type' => CardType::MULTIPLE_CHOICE->value,
            'question' => "Question {$i}",
            'answer_choices' => json_encode(['A', 'B', 'C', 'D']),
            'correct_answer_indices' => json_encode([0]),
        ]);
    }

    $action = new ReviewCardAction;

    // Review all 10 cards correctly
    foreach ($cards as $card) {
        $action->execute($user->id, $card->id, ['A']);
    }

    // Each card should be at stage 1
    foreach ($cards as $card) {
        $review = CardReview::where('user_id', $user->id)->where('card_id', $card->id)->first();
        expect($review->srs_stage)->toBe(1);
        // next_review_at should be ~4 hours from now
        expect($review->next_review_at)->not->toBeNull();
    }

    // Accuracy should be 100% (10 correct / 10 total)
    $historyCount = ReviewHistory::where('user_id', $user->id)->where('is_practice', false)->count();
    $correctCount = ReviewHistory::where('user_id', $user->id)->where('is_practice', false)->where('is_correct', true)->count();
    expect($historyCount)->toBe(10);
    expect($correctCount)->toBe(10);

    // Mastery rate should be 0% (no cards at stage >= 7)
    $masteredCount = CardReview::where('user_id', $user->id)
        ->where('srs_stage', '>=', SrsStage::MASTERED_THRESHOLD)
        ->count();
    expect($masteredCount)->toBe(0);
});

test('Scenario B: Single card reaching Professor (stage 7)', function () {
    $user = User::factory()->create();
    $deck = Deck::factory()->create();
    $card = Card::factory()->create([
        'deck_id' => $deck->id,
        'card_type' => CardType::MULTIPLE_CHOICE->value,
        'question' => 'Test question',
        'answer_choices' => json_encode(['A', 'B', 'C', 'D']),
        'correct_answer_indices' => json_encode([0]),
    ]);

    $action = new ReviewCardAction;

    // Answer correctly 7 times: 0 -> 1 -> 2 -> 3 -> 4 -> 5 -> 6 -> 7
    for ($i = 0; $i < 7; $i++) {
        $action->execute($user->id, $card->id, ['A']);
    }

    $review = CardReview::where('user_id', $user->id)->where('card_id', $card->id)->first();
    expect($review->srs_stage)->toBe(7); // Professor

    // Card should be mastered now
    expect(SrsStage::isMastered($review->srs_stage))->toBeTrue();
});

test('Scenario C: Regression from higher stage', function () {
    $user = User::factory()->create();
    $deck = Deck::factory()->create();
    $card = Card::factory()->create([
        'deck_id' => $deck->id,
        'card_type' => CardType::MULTIPLE_CHOICE->value,
        'question' => 'Test question',
        'answer_choices' => json_encode(['A', 'B', 'C', 'D']),
        'correct_answer_indices' => json_encode([0]),
    ]);

    // Create existing review at stage 6 (Connoisseur II)
    CardReview::create([
        'user_id' => $user->id,
        'card_id' => $card->id,
        'srs_stage' => 6,
        'next_review_at' => now(),
    ]);

    $action = new ReviewCardAction;
    $action->execute($user->id, $card->id, ['B']); // Incorrect answer

    $review = CardReview::where('user_id', $user->id)->where('card_id', $card->id)->first();

    // Stage 6 -> 4 (high-level penalty: -2)
    expect($review->srs_stage)->toBe(4);
    // next_review_at should be ~47 hours from now (Student II interval)
    expect($review->next_review_at)->not->toBeNull();
});
