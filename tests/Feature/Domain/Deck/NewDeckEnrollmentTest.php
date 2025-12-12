<?php

declare(strict_types=1);

use Database\Factories\CardFactory;
use Database\Factories\DeckFactory;
use Database\Factories\UserFactory;
use Domain\Card\Data\StudySessionConfigData;
use Domain\Card\Enums\StudySessionType;
use Domain\Card\Repositories\CardRepository;
use Domain\Deck\Actions\EnrollUserInDeckAction;

test('newly enrolled deck has cards available for study', function () {
    $user = UserFactory::new()->create();
    $deck = DeckFactory::new()->create();

    // Create cards in the deck
    CardFactory::new()->count(20)->create(['deck_id' => $deck->id]);

    // Enroll user in deck
    (new EnrollUserInDeckAction)->execute($user->id, $deck->id);

    $repository = new CardRepository;

    // Get new cards for user
    $newCards = $repository->getNewCardsForUser($user->id);

    // All 20 cards should be available as new cards
    expect($newCards->count())->toBe(20);
    expect($newCards->every(fn ($card) => $card->deck_id === $deck->id))->toBeTrue();
});

test('newly enrolled deck works with normal session', function () {
    $user = UserFactory::new()->create();
    $deck = DeckFactory::new()->create();

    // Create cards in the deck
    CardFactory::new()->count(20)->create(['deck_id' => $deck->id]);

    // Enroll user in deck
    (new EnrollUserInDeckAction)->execute($user->id, $deck->id);

    $repository = new CardRepository;
    $config = new StudySessionConfigData(
        type: StudySessionType::NORMAL,
        cardLimit: null,
        statusFilters: null,
        trackSrs: true,
        randomOrder: false,
    );

    $sessionCards = $repository->getCardsForSession($user->id, $deck->id, $config);

    // Should return up to 10 new cards for normal session
    expect($sessionCards->count())->toBeGreaterThan(0);
    expect($sessionCards->count())->toBeLessThanOrEqual(10);
});

test('newly enrolled deck works with deep study session', function () {
    $user = UserFactory::new()->create();
    $deck = DeckFactory::new()->create();

    // Create cards in the deck
    CardFactory::new()->count(30)->create(['deck_id' => $deck->id]);

    // Enroll user in deck
    (new EnrollUserInDeckAction)->execute($user->id, $deck->id);

    $repository = new CardRepository;
    $config = new StudySessionConfigData(
        type: StudySessionType::DEEP_STUDY,
        cardLimit: null,
        statusFilters: null,
        trackSrs: true,
        randomOrder: false,
    );

    $sessionCards = $repository->getCardsForSession($user->id, $deck->id, $config);

    // Should return all 30 cards for deep study
    expect($sessionCards->count())->toBe(30);
});

test('user can immediately start studying after enrollment', function () {
    $user = UserFactory::new()->create();
    $deck = DeckFactory::new()->create();

    // Create some cards
    $cards = CardFactory::new()->count(10)->create(['deck_id' => $deck->id]);

    // Enroll user
    $shortcode = (new EnrollUserInDeckAction)->execute($user->id, $deck->id);

    // Immediately check if cards are available
    $repository = new CardRepository;
    $newCards = $repository->getNewCardsForUser($user->id);

    expect($newCards->count())->toBe(10);
    expect($shortcode)->toBeString();
    expect(strlen($shortcode))->toBe(8);
});

test('getNewCardsForUser filters out reviewed cards', function () {
    $user = UserFactory::new()->create();
    $deck = DeckFactory::new()->create();

    // Enroll user in deck
    (new EnrollUserInDeckAction)->execute($user->id, $deck->id);

    // Create cards
    $cards = CardFactory::new()->count(10)->create(['deck_id' => $deck->id]);

    // Review 5 cards
    foreach ($cards->take(5) as $card) {
        \Database\Factories\CardReviewFactory::new()->create([
            'user_id' => $user->id,
            'card_id' => $card->id,
        ]);
    }

    $repository = new CardRepository;
    $newCards = $repository->getNewCardsForUser($user->id);

    // Should only return 5 unreviewed cards
    expect($newCards->count())->toBe(5);
});

test('getNewCardsForUser respects limit parameter', function () {
    $user = UserFactory::new()->create();
    $deck = DeckFactory::new()->create();

    // Enroll user in deck
    (new EnrollUserInDeckAction)->execute($user->id, $deck->id);

    // Create many cards
    CardFactory::new()->count(50)->create(['deck_id' => $deck->id]);

    $repository = new CardRepository;
    $newCards = $repository->getNewCardsForUser($user->id, limit: 10);

    // Should only return 10 cards
    expect($newCards->count())->toBe(10);
});

test('multiple deck enrollment works correctly', function () {
    $user = UserFactory::new()->create();
    $deck1 = DeckFactory::new()->create();
    $deck2 = DeckFactory::new()->create();

    // Create cards in both decks
    CardFactory::new()->count(10)->create(['deck_id' => $deck1->id]);
    CardFactory::new()->count(15)->create(['deck_id' => $deck2->id]);

    // Enroll in both decks
    (new EnrollUserInDeckAction)->execute($user->id, $deck1->id);
    (new EnrollUserInDeckAction)->execute($user->id, $deck2->id);

    $repository = new CardRepository;

    // Get cards for deck 1
    $config = new StudySessionConfigData(
        type: StudySessionType::DEEP_STUDY,
        cardLimit: null,
        statusFilters: null,
        trackSrs: true,
        randomOrder: false,
    );

    $deck1Cards = $repository->getCardsForSession($user->id, $deck1->id, $config);
    $deck2Cards = $repository->getCardsForSession($user->id, $deck2->id, $config);

    // Each deck should have its own cards
    expect($deck1Cards->count())->toBe(10);
    expect($deck2Cards->count())->toBe(15);
});
