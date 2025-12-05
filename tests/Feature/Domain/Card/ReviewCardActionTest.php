<?php

declare(strict_types=1);

use Domain\Card\Actions\ReviewCardAction;
use Domain\Card\Enums\CardRating;
use Domain\Card\Enums\CardType;
use Domain\Card\Models\Card;
use Domain\Card\Models\CardReview;
use Domain\Deck\Models\Deck;
use Domain\User\Models\User;

test('review card action creates new review for first time with correct answer', function () {
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
    expect($reviewData->rating)->toBe(CardRating::CORRECT->value);
    expect($reviewData->is_correct)->toBeTrue();

    $review = CardReview::where('user_id', $user->id)
        ->where('card_id', $card->id)
        ->first();

    expect($review)->not->toBeNull();
    // After a correct answer, ease factor increases from 2.5 to 2.6
    expect((float) $review->ease_factor)->toBe(2.6);
});

test('review card action creates new review for incorrect answer', function () {
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

    expect($reviewData->rating)->toBe(CardRating::INCORRECT->value);
    expect($reviewData->is_correct)->toBeFalse();
});

test('review card action updates existing review', function () {
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

    $action->execute($user->id, $card->id, ['London']); // Incorrect first
    $reviewData = $action->execute($user->id, $card->id, ['Paris']); // Correct second

    expect($reviewData->rating)->toBe(CardRating::CORRECT->value);
    expect($reviewData->is_correct)->toBeTrue();
});

test('review card action sets correct next review time for incorrect answer', function () {
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

    $review = CardReview::find($reviewData->id);
    // Should be 4 hours from now
    $hoursUntilReview = now()->diffInHours($review->next_review_at, false);
    expect($hoursUntilReview)->toBeLessThanOrEqual(4);
    expect($hoursUntilReview)->toBeGreaterThanOrEqual(3);
});

test('review card action sets correct next review time for correct answer', function () {
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

    $review = CardReview::find($reviewData->id);
    // Should be 1 day from now for first correct answer
    expect($review->next_review_at)->toBeGreaterThan(now());
    expect(now()->diffInHours($review->next_review_at, false))->toBeGreaterThanOrEqual(23);
});

test('review card action adjusts ease factor correctly', function () {
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

    $firstReview = $action->execute($user->id, $card->id, ['London']); // Incorrect
    expect($firstReview->ease_factor)->toBeLessThan('2.5');

    $secondReview = $action->execute($user->id, $card->id, ['Paris']); // Correct
    expect((float) $secondReview->ease_factor)->toBeGreaterThan((float) $firstReview->ease_factor);
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

    expect($reviewData->rating)->toBe(CardRating::CORRECT->value);
    expect($reviewData->is_correct)->toBeTrue();
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

    expect($reviewData->rating)->toBe(CardRating::INCORRECT->value);
    expect($reviewData->is_correct)->toBeFalse();
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
    $reviewData = $action->execute($user->id, $card->id, ['Merlot', 'Syrah', 'Chardonnay']);

    expect($reviewData->rating)->toBe(CardRating::INCORRECT->value);
    expect($reviewData->is_correct)->toBeFalse();
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
    $reviewData = $action->execute($user->id, $card->id, ['Syrah', 'Merlot']);

    expect($reviewData->rating)->toBe(CardRating::CORRECT->value);
    expect($reviewData->is_correct)->toBeTrue();
});
