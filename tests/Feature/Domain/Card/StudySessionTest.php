<?php

declare(strict_types=1);

use Database\Factories\CardFactory;
use Database\Factories\CardReviewFactory;
use Database\Factories\DeckFactory;
use Database\Factories\UserFactory;
use Domain\Card\Data\StudySessionConfigData;
use Domain\Card\Enums\StudySessionType;
use Domain\Card\Repositories\CardRepository;
use Domain\Deck\Actions\EnrollUserInDeckAction;

test('getCardsForSession returns cards for normal session', function () {
    $user = UserFactory::new()->create();
    $deck = DeckFactory::new()->create();
    
    // Enroll user in deck
    (new EnrollUserInDeckAction())->execute($user->id, $deck->id);
    
    // Create cards
    $cards = CardFactory::new()->count(15)->create(['deck_id' => $deck->id]);
    
    // Create some reviews to make cards due
    foreach ($cards->take(5) as $card) {
        CardReviewFactory::new()->create([
            'user_id' => $user->id,
            'card_id' => $card->id,
            'next_review_at' => now()->subDay(),
        ]);
    }
    
    $repository = new CardRepository();
    $config = new StudySessionConfigData(
        type: StudySessionType::NORMAL,
        cardLimit: null,
        statusFilters: null,
        trackSrs: true,
        randomOrder: false,
    );
    
    $sessionCards = $repository->getCardsForSession($user->id, $deck->id, $config);
    
    // Normal session should return ~10 cards (5 due + 5 new)
    expect($sessionCards->count())->toBeLessThanOrEqual(10);
    expect($sessionCards->count())->toBeGreaterThan(0);
});

test('getCardsForSession returns all cards for deep study', function () {
    $user = UserFactory::new()->create();
    $deck = DeckFactory::new()->create();
    
    // Enroll user in deck
    (new EnrollUserInDeckAction())->execute($user->id, $deck->id);
    
    // Create cards
    $cards = CardFactory::new()->count(20)->create(['deck_id' => $deck->id]);
    
    // Create some reviews
    foreach ($cards->take(10) as $card) {
        CardReviewFactory::new()->create([
            'user_id' => $user->id,
            'card_id' => $card->id,
            'next_review_at' => now()->subDay(),
        ]);
    }
    
    $repository = new CardRepository();
    $config = new StudySessionConfigData(
        type: StudySessionType::DEEP_STUDY,
        cardLimit: null,
        statusFilters: null,
        trackSrs: true,
        randomOrder: false,
    );
    
    $sessionCards = $repository->getCardsForSession($user->id, $deck->id, $config);
    
    // Deep study should return all 20 cards
    expect($sessionCards->count())->toBe(20);
});

test('getCardsForSession filters by mistakes for practice session', function () {
    $user = UserFactory::new()->create();
    $deck = DeckFactory::new()->create();
    
    // Enroll user in deck
    (new EnrollUserInDeckAction())->execute($user->id, $deck->id);
    
    // Create cards
    $cards = CardFactory::new()->count(10)->create(['deck_id' => $deck->id]);
    
    // Create reviews - some correct, some incorrect
    foreach ($cards->take(5) as $card) {
        CardReviewFactory::new()->create([
            'user_id' => $user->id,
            'card_id' => $card->id,
            'is_correct' => false, // Mistakes
        ]);
    }
    
    foreach ($cards->skip(5)->take(5) as $card) {
        CardReviewFactory::new()->create([
            'user_id' => $user->id,
            'card_id' => $card->id,
            'is_correct' => true, // Correct
        ]);
    }
    
    $repository = new CardRepository();
    $config = new StudySessionConfigData(
        type: StudySessionType::PRACTICE,
        cardLimit: null,
        statusFilters: ['mistakes'],
        trackSrs: false,
        randomOrder: false,
    );
    
    $sessionCards = $repository->getCardsForSession($user->id, $deck->id, $config);
    
    // Should only return the 5 mistake cards
    expect($sessionCards->count())->toBe(5);
});

test('getCardsForSession filters by new cards for practice session', function () {
    $user = UserFactory::new()->create();
    $deck = DeckFactory::new()->create();
    
    // Enroll user in deck
    (new EnrollUserInDeckAction())->execute($user->id, $deck->id);
    
    // Create cards
    $cards = CardFactory::new()->count(10)->create(['deck_id' => $deck->id]);
    
    // Review only 5 cards, leaving 5 as "new"
    foreach ($cards->take(5) as $card) {
        CardReviewFactory::new()->create([
            'user_id' => $user->id,
            'card_id' => $card->id,
        ]);
    }
    
    $repository = new CardRepository();
    $config = new StudySessionConfigData(
        type: StudySessionType::PRACTICE,
        cardLimit: null,
        statusFilters: ['new'],
        trackSrs: false,
        randomOrder: false,
    );
    
    $sessionCards = $repository->getCardsForSession($user->id, $deck->id, $config);
    
    // Should only return the 5 new cards
    expect($sessionCards->count())->toBe(5);
});

test('getCardsForSession filters by mastered cards for practice session', function () {
    $user = UserFactory::new()->create();
    $deck = DeckFactory::new()->create();
    
    // Enroll user in deck
    (new EnrollUserInDeckAction())->execute($user->id, $deck->id);
    
    // Create cards
    $cards = CardFactory::new()->count(10)->create(['deck_id' => $deck->id]);
    
    // Create reviews - some mastered (high ease factor)
    foreach ($cards->take(3) as $card) {
        CardReviewFactory::new()->create([
            'user_id' => $user->id,
            'card_id' => $card->id,
            'ease_factor' => 2.8, // Mastered
        ]);
    }
    
    foreach ($cards->skip(3)->take(7) as $card) {
        CardReviewFactory::new()->create([
            'user_id' => $user->id,
            'card_id' => $card->id,
            'ease_factor' => 2.0, // Not mastered
        ]);
    }
    
    $repository = new CardRepository();
    $config = new StudySessionConfigData(
        type: StudySessionType::PRACTICE,
        cardLimit: null,
        statusFilters: ['mastered'],
        trackSrs: false,
        randomOrder: false,
    );
    
    $sessionCards = $repository->getCardsForSession($user->id, $deck->id, $config);
    
    // Should only return the 3 mastered cards
    expect($sessionCards->count())->toBe(3);
});

test('getCardsForSession applies card limit', function () {
    $user = UserFactory::new()->create();
    $deck = DeckFactory::new()->create();
    
    // Enroll user in deck
    (new EnrollUserInDeckAction())->execute($user->id, $deck->id);
    
    // Create cards
    CardFactory::new()->count(30)->create(['deck_id' => $deck->id]);
    
    $repository = new CardRepository();
    $config = new StudySessionConfigData(
        type: StudySessionType::DEEP_STUDY,
        cardLimit: 10,
        statusFilters: null,
        trackSrs: true,
        randomOrder: false,
    );
    
    $sessionCards = $repository->getCardsForSession($user->id, $deck->id, $config);
    
    // Should only return 10 cards due to limit
    expect($sessionCards->count())->toBe(10);
});

test('getCardsForSession applies random order', function () {
    $user = UserFactory::new()->create();
    $deck = DeckFactory::new()->create();
    
    // Enroll user in deck
    (new EnrollUserInDeckAction())->execute($user->id, $deck->id);
    
    // Create cards
    CardFactory::new()->count(20)->create(['deck_id' => $deck->id]);
    
    $repository = new CardRepository();
    $config = new StudySessionConfigData(
        type: StudySessionType::DEEP_STUDY,
        cardLimit: null,
        statusFilters: null,
        trackSrs: true,
        randomOrder: true,
    );
    
    $sessionCards1 = $repository->getCardsForSession($user->id, $deck->id, $config);
    $sessionCards2 = $repository->getCardsForSession($user->id, $deck->id, $config);
    
    // Due to randomization, the order should differ (very high probability)
    $ids1 = $sessionCards1->pluck('id')->toArray();
    $ids2 = $sessionCards2->pluck('id')->toArray();
    
    expect($ids1)->not->toBe($ids2);
});

test('getCardsForSession only returns cards from enrolled decks', function () {
    $user = UserFactory::new()->create();
    $enrolledDeck = DeckFactory::new()->create();
    $unenrolledDeck = DeckFactory::new()->create();
    
    // Enroll user only in first deck
    (new EnrollUserInDeckAction())->execute($user->id, $enrolledDeck->id);
    
    // Create cards in both decks
    CardFactory::new()->count(5)->create(['deck_id' => $enrolledDeck->id]);
    CardFactory::new()->count(5)->create(['deck_id' => $unenrolledDeck->id]);
    
    $repository = new CardRepository();
    $config = new StudySessionConfigData(
        type: StudySessionType::DEEP_STUDY,
        cardLimit: null,
        statusFilters: null,
        trackSrs: true,
        randomOrder: false,
    );
    
    $sessionCards = $repository->getCardsForSession($user->id, $enrolledDeck->id, $config);
    
    // Should only return cards from enrolled deck
    expect($sessionCards->count())->toBe(5);
    expect($sessionCards->every(fn ($card) => $card->deck_id === $enrolledDeck->id))->toBeTrue();
});

