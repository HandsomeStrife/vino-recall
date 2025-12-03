<?php

declare(strict_types=1);

use App\Livewire\Library;
use Domain\Card\Models\Card;
use Domain\Card\Models\CardReview;
use Domain\Deck\Models\Deck;
use Domain\User\Models\User;
use Livewire\Livewire;

test('library can be rendered', function () {
    $user = actingAsUser();

    Livewire::test(Library::class)
        ->assertStatus(200)
        ->assertSee('Library');
});

test('library shows active decks', function () {
    $user = actingAsUser();
    $deck1 = Deck::factory()->create(['name' => 'Deck 1', 'is_active' => true]);
    $deck2 = Deck::factory()->create(['name' => 'Deck 2', 'is_active' => true]);
    $deck3 = Deck::factory()->create(['name' => 'Deck 3', 'is_active' => false]);

    Livewire::test(Library::class)
        ->assertSee('Deck 1')
        ->assertSee('Deck 2')
        ->assertDontSee('Deck 3');
});

test('library shows deck progress', function () {
    $user = actingAsUser();
    $deck = Deck::factory()->create();
    $card1 = Card::factory()->create(['deck_id' => $deck->id]);
    $card2 = Card::factory()->create(['deck_id' => $deck->id]);

    CardReview::factory()->create([
        'user_id' => $user->id,
        'card_id' => $card1->id,
    ]);

    Livewire::test(Library::class)
        ->assertSee('1 / 2 cards');
});

// Edge Case Tests

test('library shows empty state when no decks exist', function () {
    $user = actingAsUser();

    Livewire::test(Library::class)
        ->assertSee('Library')
        ->assertStatus(200);
});

test('library only shows active decks not inactive', function () {
    $user = actingAsUser();
    $activeDeck = Deck::factory()->create(['is_active' => true, 'name' => 'Active Deck']);
    $inactiveDeck = Deck::factory()->create(['is_active' => false, 'name' => 'Inactive Deck']);

    Livewire::test(Library::class)
        ->assertSee('Active Deck')
        ->assertDontSee('Inactive Deck');
});

test('library shows 0 progress for deck with no reviews', function () {
    $user = actingAsUser();
    $deck = Deck::factory()->create(['is_active' => true, 'name' => 'New Deck']);
    Card::factory()->count(5)->create(['deck_id' => $deck->id]);

    Livewire::test(Library::class)
        ->assertSee('New Deck')
        ->assertSee('0 / 5 cards');
});

test('library shows full progress for fully reviewed deck', function () {
    $user = actingAsUser();
    $deck = Deck::factory()->create(['is_active' => true, 'name' => 'Complete Deck']);
    $cards = Card::factory()->count(3)->create(['deck_id' => $deck->id]);

    // Review all cards
    foreach ($cards as $card) {
        CardReview::factory()->create([
            'user_id' => $user->id,
            'card_id' => $card->id,
        ]);
    }

    Livewire::test(Library::class)
        ->assertSee('Complete Deck')
        ->assertSee('3 / 3 cards');
});

test('library handles deck with no cards', function () {
    $user = actingAsUser();
    $deck = Deck::factory()->create(['is_active' => true, 'name' => 'Empty Deck']);
    // No cards in this deck

    Livewire::test(Library::class)
        ->assertSee('Empty Deck')
        ->assertSee('0 / 0 cards');
});

test('library shows correct card count for deck', function () {
    $user = actingAsUser();
    $deck = Deck::factory()->create(['is_active' => true, 'name' => 'Counted Deck']);
    Card::factory()->count(15)->create(['deck_id' => $deck->id]);

    Livewire::test(Library::class)
        ->assertSee('Counted Deck')
        ->assertSee('0 / 15 cards');
});

test('library shows multiple decks correctly', function () {
    $user = actingAsUser();
    $deck1 = Deck::factory()->create(['is_active' => true, 'name' => 'Deck One']);
    $deck2 = Deck::factory()->create(['is_active' => true, 'name' => 'Deck Two']);
    $deck3 = Deck::factory()->create(['is_active' => true, 'name' => 'Deck Three']);

    Card::factory()->count(5)->create(['deck_id' => $deck1->id]);
    Card::factory()->count(10)->create(['deck_id' => $deck2->id]);
    Card::factory()->count(3)->create(['deck_id' => $deck3->id]);

    Livewire::test(Library::class)
        ->assertSee('Deck One')
        ->assertSee('Deck Two')
        ->assertSee('Deck Three');
});

test('library progress is user-specific', function () {
    $user1 = actingAsUser();
    $user2 = User::factory()->create();
    
    $deck = Deck::factory()->create(['is_active' => true, 'name' => 'Shared Deck']);
    $card1 = Card::factory()->create(['deck_id' => $deck->id]);
    $card2 = Card::factory()->create(['deck_id' => $deck->id]);

    // User 1 reviewed one card
    CardReview::factory()->create([
        'user_id' => $user1->id,
        'card_id' => $card1->id,
    ]);

    // User 2 reviewed both cards
    CardReview::factory()->create([
        'user_id' => $user2->id,
        'card_id' => $card1->id,
    ]);
    CardReview::factory()->create([
        'user_id' => $user2->id,
        'card_id' => $card2->id,
    ]);

    // User 1 should see 1/2 progress
    Livewire::test(Library::class)
        ->assertSee('Shared Deck')
        ->assertSee('1 / 2 cards');
});

test('library study deck link is present', function () {
    $user = actingAsUser();
    $deck = Deck::factory()->create(['is_active' => true, 'name' => 'Link Deck']);
    Card::factory()->create(['deck_id' => $deck->id]);

    Livewire::test(Library::class)
        ->assertSee('Study Deck')
        ->assertSee('Link Deck');
});

test('library handles very large number of cards in deck', function () {
    $user = actingAsUser();
    $deck = Deck::factory()->create(['is_active' => true, 'name' => 'Large Deck']);
    Card::factory()->count(500)->create(['deck_id' => $deck->id]);

    Livewire::test(Library::class)
        ->assertSee('Large Deck')
        ->assertSee('0 / 500 cards')
        ->assertStatus(200);
});
