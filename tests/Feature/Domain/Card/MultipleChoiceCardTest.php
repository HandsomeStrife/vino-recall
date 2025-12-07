<?php

declare(strict_types=1);

use Domain\Admin\Models\Admin;
use Domain\Card\Actions\CreateCardAction;
use Domain\Card\Actions\ReviewCardAction;
use Domain\Card\Actions\UpdateCardAction;
use Domain\Card\Enums\CardType;
use Domain\Card\Models\Card;
use Domain\Card\Models\CardReview;
use Domain\Card\Models\ReviewHistory;
use Domain\Deck\Models\Deck;
use Domain\User\Models\User;

test('can create a multiple choice card with single correct answer', function (): void {
    $admin = Admin::factory()->create();
    $deck = Deck::factory()->create(['created_by' => $admin->id]);

    $action = new CreateCardAction();

    $cardData = $action->execute(
        deckId: $deck->id,
        question: 'What is the capital of France?',
        answer: 'Paris',
        image_path: null,
        cardType: CardType::MULTIPLE_CHOICE,
        answerChoices: ['London', 'Paris', 'Berlin', 'Rome'],
        correctAnswerIndices: [1]
    );

    expect($cardData->card_type)->toBe(CardType::MULTIPLE_CHOICE)
        ->and($cardData->question)->toBe('What is the capital of France?')
        ->and($cardData->answer)->toBe('Paris')
        ->and($cardData->answer_choices)->toBe(['London', 'Paris', 'Berlin', 'Rome'])
        ->and($cardData->correct_answer_indices)->toBe([1]);

    $this->assertDatabaseHas('cards', [
        'id' => $cardData->id,
        'card_type' => 'multiple_choice',
        'question' => 'What is the capital of France?',
    ]);
});

test('can create a multiple choice card with multiple correct answers', function (): void {
    $admin = Admin::factory()->create();
    $deck = Deck::factory()->create(['created_by' => $admin->id]);

    $action = new CreateCardAction();

    $cardData = $action->execute(
        deckId: $deck->id,
        question: 'Which are red grape varieties?',
        answer: 'Merlot, Syrah',
        image_path: null,
        cardType: CardType::MULTIPLE_CHOICE,
        answerChoices: ['Chardonnay', 'Merlot', 'Riesling', 'Syrah'],
        correctAnswerIndices: [1, 3]
    );

    expect($cardData->card_type)->toBe(CardType::MULTIPLE_CHOICE)
        ->and($cardData->correct_answer_indices)->toBe([1, 3])
        ->and($cardData->hasMultipleCorrectAnswers())->toBeTrue();
});

test('card requires at least 2 answer choices', function (): void {
    $admin = Admin::factory()->create();
    $deck = Deck::factory()->create(['created_by' => $admin->id]);

    $action = new CreateCardAction();

    $action->execute(
        deckId: $deck->id,
        question: 'What is the capital of France?',
        answer: 'Paris',
        image_path: null,
        cardType: CardType::MULTIPLE_CHOICE,
        answerChoices: ['Paris'],
        correctAnswerIndices: [0]
    );
})->throws(\InvalidArgumentException::class, 'Cards must have at least 2 answer choices.');

test('card requires at least one correct answer index', function (): void {
    $admin = Admin::factory()->create();
    $deck = Deck::factory()->create(['created_by' => $admin->id]);

    $action = new CreateCardAction();

    $action->execute(
        deckId: $deck->id,
        question: 'What is the capital of France?',
        answer: 'Paris',
        image_path: null,
        cardType: CardType::MULTIPLE_CHOICE,
        answerChoices: ['London', 'Paris', 'Berlin'],
        correctAnswerIndices: []
    );
})->throws(\InvalidArgumentException::class, 'Cards must have at least one correct answer index.');

test('card requires valid correct answer indices', function (): void {
    $admin = Admin::factory()->create();
    $deck = Deck::factory()->create(['created_by' => $admin->id]);

    $action = new CreateCardAction();

    $action->execute(
        deckId: $deck->id,
        question: 'What is the capital of France?',
        answer: 'Paris',
        image_path: null,
        cardType: CardType::MULTIPLE_CHOICE,
        answerChoices: ['London', 'Paris', 'Berlin'],
        correctAnswerIndices: [5]
    );
})->throws(\InvalidArgumentException::class, 'Cards must have valid correct answer indices.');

test('can update a card with new answer choices', function (): void {
    $admin = Admin::factory()->create();
    $deck = Deck::factory()->create(['created_by' => $admin->id]);

    $card = Card::create([
        'deck_id' => $deck->id,
        'card_type' => 'multiple_choice',
        'question' => 'What is the capital of France?',
        'answer' => 'Paris',
        'answer_choices' => json_encode(['A', 'B']),
        'correct_answer_indices' => json_encode([0]),
    ]);

    $action = new UpdateCardAction();

    $cardData = $action->execute(
        cardId: $card->id,
        answerChoices: ['London', 'Paris', 'Berlin', 'Rome'],
        correctAnswerIndices: [1]
    );

    expect($cardData->answer_choices)->toBe(['London', 'Paris', 'Berlin', 'Rome'])
        ->and($cardData->correct_answer_indices)->toBe([1]);
});

test('can review a card with correct single answer', function (): void {
    $user = User::factory()->create();
    $deck = Deck::factory()->create(['created_by' => $user->id]);

    $card = Card::create([
        'deck_id' => $deck->id,
        'card_type' => 'multiple_choice',
        'question' => 'What is the capital of France?',
        'answer' => 'Paris',
        'answer_choices' => json_encode(['London', 'Paris', 'Berlin', 'Rome']),
        'correct_answer_indices' => json_encode([1]),
    ]);

    $action = new ReviewCardAction();

    $reviewData = $action->execute(
        userId: $user->id,
        cardId: $card->id,
        selectedAnswers: ['Paris']
    );

    expect($reviewData->srs_stage)->toBe(1); // Advanced from 0 to 1

    // Check review history
    $history = ReviewHistory::where('user_id', $user->id)
        ->where('card_id', $card->id)
        ->first();
    expect($history->is_correct)->toBeTrue();

    // Check card review state
    $this->assertDatabaseHas('card_reviews', [
        'user_id' => $user->id,
        'card_id' => $card->id,
        'srs_stage' => 1,
    ]);
});

test('can review a card with incorrect single answer', function (): void {
    $user = User::factory()->create();
    $deck = Deck::factory()->create(['created_by' => $user->id]);

    $card = Card::create([
        'deck_id' => $deck->id,
        'card_type' => 'multiple_choice',
        'question' => 'What is the capital of France?',
        'answer' => 'Paris',
        'answer_choices' => json_encode(['London', 'Paris', 'Berlin', 'Rome']),
        'correct_answer_indices' => json_encode([1]),
    ]);

    $action = new ReviewCardAction();

    $reviewData = $action->execute(
        userId: $user->id,
        cardId: $card->id,
        selectedAnswers: ['London']
    );

    // Check review history
    $history = ReviewHistory::where('user_id', $user->id)
        ->where('card_id', $card->id)
        ->first();
    expect($history->is_correct)->toBeFalse();

    // Check card review state (incorrect from stage 0 goes to stage 1)
    $this->assertDatabaseHas('card_reviews', [
        'user_id' => $user->id,
        'card_id' => $card->id,
        'srs_stage' => 1,
    ]);
});

test('multi-answer card requires all correct answers selected', function (): void {
    $user = User::factory()->create();
    $deck = Deck::factory()->create(['created_by' => $user->id]);

    $card = Card::create([
        'deck_id' => $deck->id,
        'card_type' => 'multiple_choice',
        'question' => 'Which are red grapes?',
        'answer' => 'Merlot, Syrah',
        'answer_choices' => json_encode(['Chardonnay', 'Merlot', 'Riesling', 'Syrah']),
        'correct_answer_indices' => json_encode([1, 3]),
    ]);

    $action = new ReviewCardAction();

    // Only selected one of two correct answers - should be incorrect (all-or-nothing)
    $action->execute(
        userId: $user->id,
        cardId: $card->id,
        selectedAnswers: ['Merlot']
    );

    $history = ReviewHistory::where('user_id', $user->id)
        ->where('card_id', $card->id)
        ->first();
    expect($history->is_correct)->toBeFalse();
});

test('multi-answer card is correct when all answers selected', function (): void {
    $user = User::factory()->create();
    $deck = Deck::factory()->create(['created_by' => $user->id]);

    $card = Card::create([
        'deck_id' => $deck->id,
        'card_type' => 'multiple_choice',
        'question' => 'Which are red grapes?',
        'answer' => 'Merlot, Syrah',
        'answer_choices' => json_encode(['Chardonnay', 'Merlot', 'Riesling', 'Syrah']),
        'correct_answer_indices' => json_encode([1, 3]),
    ]);

    $action = new ReviewCardAction();

    $action->execute(
        userId: $user->id,
        cardId: $card->id,
        selectedAnswers: ['Merlot', 'Syrah']
    );

    $history = ReviewHistory::where('user_id', $user->id)
        ->where('card_id', $card->id)
        ->first();
    expect($history->is_correct)->toBeTrue();
});

test('multi-answer card is incorrect when extra wrong answer selected', function (): void {
    $user = User::factory()->create();
    $deck = Deck::factory()->create(['created_by' => $user->id]);

    $card = Card::create([
        'deck_id' => $deck->id,
        'card_type' => 'multiple_choice',
        'question' => 'Which are red grapes?',
        'answer' => 'Merlot, Syrah',
        'answer_choices' => json_encode(['Chardonnay', 'Merlot', 'Riesling', 'Syrah']),
        'correct_answer_indices' => json_encode([1, 3]),
    ]);

    $action = new ReviewCardAction();

    // Selected both correct answers + one wrong
    $action->execute(
        userId: $user->id,
        cardId: $card->id,
        selectedAnswers: ['Merlot', 'Syrah', 'Chardonnay']
    );

    $history = ReviewHistory::where('user_id', $user->id)
        ->where('card_id', $card->id)
        ->first();
    expect($history->is_correct)->toBeFalse();
});

test('hasMultipleCorrectAnswers returns false for single correct answer', function (): void {
    $admin = Admin::factory()->create();
    $deck = Deck::factory()->create(['created_by' => $admin->id]);

    $action = new CreateCardAction();

    $cardData = $action->execute(
        deckId: $deck->id,
        question: 'What is the capital of France?',
        answer: 'Paris',
        cardType: CardType::MULTIPLE_CHOICE,
        answerChoices: ['London', 'Paris', 'Berlin', 'Rome'],
        correctAnswerIndices: [1]
    );

    expect($cardData->hasMultipleCorrectAnswers())->toBeFalse();
});

test('review with no answer selected is incorrect', function (): void {
    $user = User::factory()->create();
    $deck = Deck::factory()->create(['created_by' => $user->id]);

    $card = Card::create([
        'deck_id' => $deck->id,
        'card_type' => 'multiple_choice',
        'question' => 'What is the capital of France?',
        'answer' => 'Paris',
        'answer_choices' => json_encode(['London', 'Paris', 'Berlin', 'Rome']),
        'correct_answer_indices' => json_encode([1]),
    ]);

    $action = new ReviewCardAction();

    $action->execute(
        userId: $user->id,
        cardId: $card->id,
        selectedAnswers: []
    );

    $history = ReviewHistory::where('user_id', $user->id)
        ->where('card_id', $card->id)
        ->first();
    expect($history->is_correct)->toBeFalse();
});
