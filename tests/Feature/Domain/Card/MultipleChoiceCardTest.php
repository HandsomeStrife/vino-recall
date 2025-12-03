<?php

declare(strict_types=1);

use Domain\Admin\Models\Admin;
use Domain\Card\Actions\CreateCardAction;
use Domain\Card\Actions\ReviewCardAction;
use Domain\Card\Actions\UpdateCardAction;
use Domain\Card\Enums\CardRating;
use Domain\Card\Enums\CardType;
use Domain\Card\Models\Card;
use Domain\Deck\Models\Deck;
use Domain\User\Models\User;

test('can create a multiple choice card', function (): void {
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
        correctAnswerIndex: 1
    );

    expect($cardData->card_type)->toBe(CardType::MULTIPLE_CHOICE)
        ->and($cardData->question)->toBe('What is the capital of France?')
        ->and($cardData->answer)->toBe('Paris')
        ->and($cardData->answer_choices)->toBe(['London', 'Paris', 'Berlin', 'Rome'])
        ->and($cardData->correct_answer_index)->toBe(1);

    $this->assertDatabaseHas('cards', [
        'id' => $cardData->id,
        'card_type' => 'multiple_choice',
        'question' => 'What is the capital of France?',
        'correct_answer_index' => 1,
    ]);
});

test('can create a traditional card', function (): void {
    $admin = Admin::factory()->create();
    $deck = Deck::factory()->create(['created_by' => $admin->id]);

    $action = new CreateCardAction();

    $cardData = $action->execute(
        deckId: $deck->id,
        question: 'What is the capital of France?',
        answer: 'Paris',
        image_path: null,
        cardType: CardType::TRADITIONAL
    );

    expect($cardData->card_type)->toBe(CardType::TRADITIONAL)
        ->and($cardData->answer_choices)->toBeNull()
        ->and($cardData->correct_answer_index)->toBeNull();
});

test('multiple choice card requires at least 2 answer choices', function (): void {
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
        correctAnswerIndex: 0
    );
})->throws(\InvalidArgumentException::class, 'Multiple choice cards must have at least 2 answer choices.');

test('multiple choice card requires valid correct answer index', function (): void {
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
        correctAnswerIndex: 5
    );
})->throws(\InvalidArgumentException::class, 'Multiple choice cards must have a valid correct answer index.');

test('can update a card from traditional to multiple choice', function (): void {
    $admin = Admin::factory()->create();
    $deck = Deck::factory()->create(['created_by' => $admin->id]);

    $card = Card::create([
        'deck_id' => $deck->id,
        'card_type' => 'traditional',
        'question' => 'What is the capital of France?',
        'answer' => 'Paris',
    ]);

    $action = new UpdateCardAction();

    $cardData = $action->execute(
        cardId: $card->id,
        cardType: CardType::MULTIPLE_CHOICE,
        answerChoices: ['London', 'Paris', 'Berlin', 'Rome'],
        correctAnswerIndex: 1
    );

    expect($cardData->card_type)->toBe(CardType::MULTIPLE_CHOICE)
        ->and($cardData->answer_choices)->toBe(['London', 'Paris', 'Berlin', 'Rome'])
        ->and($cardData->correct_answer_index)->toBe(1);
});

test('can review a multiple choice card with selected answer', function (): void {
    $user = User::factory()->create();
    $deck = Deck::factory()->create(['created_by' => $user->id]);

    $card = Card::create([
        'deck_id' => $deck->id,
        'card_type' => 'multiple_choice',
        'question' => 'What is the capital of France?',
        'answer' => 'Paris',
        'answer_choices' => json_encode(['London', 'Paris', 'Berlin', 'Rome']),
        'correct_answer_index' => 1,
    ]);

    $action = new ReviewCardAction();

    $reviewData = $action->execute(
        userId: $user->id,
        cardId: $card->id,
        rating: CardRating::GOOD,
        selectedAnswer: 'Paris'
    );

    expect($reviewData->selected_answer)->toBe('Paris')
        ->and($reviewData->rating)->toBe('good');

    $this->assertDatabaseHas('card_reviews', [
        'user_id' => $user->id,
        'card_id' => $card->id,
        'rating' => 'good',
        'selected_answer' => 'Paris',
    ]);
});

test('can review a multiple choice card with incorrect answer', function (): void {
    $user = User::factory()->create();
    $deck = Deck::factory()->create(['created_by' => $user->id]);

    $card = Card::create([
        'deck_id' => $deck->id,
        'card_type' => 'multiple_choice',
        'question' => 'What is the capital of France?',
        'answer' => 'Paris',
        'answer_choices' => json_encode(['London', 'Paris', 'Berlin', 'Rome']),
        'correct_answer_index' => 1,
    ]);

    $action = new ReviewCardAction();

    $reviewData = $action->execute(
        userId: $user->id,
        cardId: $card->id,
        rating: CardRating::AGAIN,
        selectedAnswer: 'London'
    );

    expect($reviewData->selected_answer)->toBe('London')
        ->and($reviewData->rating)->toBe('again');

    $this->assertDatabaseHas('card_reviews', [
        'user_id' => $user->id,
        'card_id' => $card->id,
        'rating' => 'again',
        'selected_answer' => 'London',
    ]);
});

test('traditional card review can have null selected answer', function (): void {
    $user = User::factory()->create();
    $deck = Deck::factory()->create(['created_by' => $user->id]);

    $card = Card::create([
        'deck_id' => $deck->id,
        'card_type' => 'traditional',
        'question' => 'What is the capital of France?',
        'answer' => 'Paris',
    ]);

    $action = new ReviewCardAction();

    $reviewData = $action->execute(
        userId: $user->id,
        cardId: $card->id,
        rating: CardRating::GOOD,
        selectedAnswer: null
    );

    expect($reviewData->selected_answer)->toBeNull()
        ->and($reviewData->rating)->toBe('good');
});

