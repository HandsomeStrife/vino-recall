<?php

declare(strict_types=1);

use Domain\Deck\Actions\EnrollUserInDeckAction;
use Domain\Deck\Actions\UnenrollUserFromDeckAction;
use Domain\Deck\Models\Deck;
use Domain\Deck\Repositories\DeckRepository;
use Domain\User\Models\User;

test('user can enroll in a deck', function () {
    $user = User::factory()->create();
    $deck = Deck::factory()->create();

    $action = new EnrollUserInDeckAction();
    $action->execute($user->id, $deck->id);

    expect($user->enrolledDecks()->where('deck_id', $deck->id)->exists())->toBeTrue();
});

test('user can unenroll from a deck', function () {
    $user = User::factory()->create();
    $deck = Deck::factory()->create();

    // First enroll
    $user->enrolledDecks()->attach($deck->id, ['enrolled_at' => now()]);

    // Then unenroll
    $action = new UnenrollUserFromDeckAction();
    $action->execute($user->id, $deck->id);

    expect($user->enrolledDecks()->where('deck_id', $deck->id)->exists())->toBeFalse();
});

test('enrolling in same deck multiple times is idempotent', function () {
    $user = User::factory()->create();
    $deck = Deck::factory()->create();

    $action = new EnrollUserInDeckAction();
    $action->execute($user->id, $deck->id);
    $action->execute($user->id, $deck->id);

    expect($user->enrolledDecks()->where('deck_id', $deck->id)->count())->toBe(1);
});

test('repository can check if user is enrolled in deck', function () {
    $user = User::factory()->create();
    $deck = Deck::factory()->create();

    $repository = new DeckRepository();

    expect($repository->isUserEnrolledInDeck($user->id, $deck->id))->toBeFalse();

    $user->enrolledDecks()->attach($deck->id, ['enrolled_at' => now()]);

    expect($repository->isUserEnrolledInDeck($user->id, $deck->id))->toBeTrue();
});

test('repository can get user enrolled decks', function () {
    $user = User::factory()->create();
    $deck1 = Deck::factory()->create();
    $deck2 = Deck::factory()->create();
    $deck3 = Deck::factory()->create();

    $user->enrolledDecks()->attach([$deck1->id, $deck2->id], ['enrolled_at' => now()]);

    $repository = new DeckRepository();
    $enrolledDecks = $repository->getUserEnrolledDecks($user->id);

    expect($enrolledDecks)->toHaveCount(2);
    expect($enrolledDecks->pluck('id')->toArray())->toContain($deck1->id, $deck2->id);
    expect($enrolledDecks->pluck('id')->toArray())->not->toContain($deck3->id);
});

