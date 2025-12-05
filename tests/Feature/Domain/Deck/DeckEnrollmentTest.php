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
    $shortcode = $action->execute($user->id, $deck->id);

    expect($user->enrolledDecks()->where('deck_id', $deck->id)->exists())->toBeTrue();
    expect($shortcode)->toBeString();
    expect(strlen($shortcode))->toBe(8);
});

test('user can unenroll from a deck', function () {
    $user = User::factory()->create();
    $deck = Deck::factory()->create();

    // First enroll using the action (which generates shortcode)
    $action = new EnrollUserInDeckAction();
    $action->execute($user->id, $deck->id);

    // Then unenroll
    $unenrollAction = new UnenrollUserFromDeckAction();
    $unenrollAction->execute($user->id, $deck->id);

    expect($user->enrolledDecks()->where('deck_id', $deck->id)->exists())->toBeFalse();
});

test('enrolling in same deck multiple times is idempotent', function () {
    $user = User::factory()->create();
    $deck = Deck::factory()->create();

    $action = new EnrollUserInDeckAction();
    $shortcode1 = $action->execute($user->id, $deck->id);
    $shortcode2 = $action->execute($user->id, $deck->id);

    expect($user->enrolledDecks()->where('deck_id', $deck->id)->count())->toBe(1);
    // Should return the same shortcode
    expect($shortcode1)->toBe($shortcode2);
});

test('repository can check if user is enrolled in deck', function () {
    $user = User::factory()->create();
    $deck = Deck::factory()->create();

    $repository = new DeckRepository();

    expect($repository->isUserEnrolledInDeck($user->id, $deck->id))->toBeFalse();

    // Use action to enroll (generates shortcode)
    $action = new EnrollUserInDeckAction();
    $action->execute($user->id, $deck->id);

    expect($repository->isUserEnrolledInDeck($user->id, $deck->id))->toBeTrue();
});

test('repository can get user enrolled decks', function () {
    $user = User::factory()->create();
    $deck1 = Deck::factory()->create();
    $deck2 = Deck::factory()->create();
    $deck3 = Deck::factory()->create();

    // Use action to enroll (generates shortcode)
    $action = new EnrollUserInDeckAction();
    $action->execute($user->id, $deck1->id);
    $action->execute($user->id, $deck2->id);

    $repository = new DeckRepository();
    $enrolledDecks = $repository->getUserEnrolledDecks($user->id);

    expect($enrolledDecks)->toHaveCount(2);
    expect($enrolledDecks->pluck('id')->toArray())->toContain($deck1->id, $deck2->id);
    expect($enrolledDecks->pluck('id')->toArray())->not->toContain($deck3->id);
});

test('enrollment generates unique shortcode per user-deck pair', function () {
    $user = User::factory()->create();
    $deck1 = Deck::factory()->create();
    $deck2 = Deck::factory()->create();

    $action = new EnrollUserInDeckAction();
    $shortcode1 = $action->execute($user->id, $deck1->id);
    $shortcode2 = $action->execute($user->id, $deck2->id);

    expect($shortcode1)->not->toBe($shortcode2);
});

test('different users get different shortcodes for same deck', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $deck = Deck::factory()->create();

    $action = new EnrollUserInDeckAction();
    $shortcode1 = $action->execute($user1->id, $deck->id);
    $shortcode2 = $action->execute($user2->id, $deck->id);

    expect($shortcode1)->not->toBe($shortcode2);
});
