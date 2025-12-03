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

test('library shows enrollment status', function () {
    $user = actingAsUser();
    $enrolledDeck = Deck::factory()->create(['name' => 'Enrolled Deck']);
    $notEnrolledDeck = Deck::factory()->create(['name' => 'Not Enrolled Deck']);
    
    // Enroll user in first deck
    $user->enrolledDecks()->attach($enrolledDeck->id, ['enrolled_at' => now()]);

    Livewire::test(Library::class)
        ->assertSee('Enrolled Deck')
        ->assertSee('✓ Enrolled')
        ->assertSee('Not Enrolled Deck')
        ->assertSee('Add to My Library');
});

test('user can enroll in deck from library', function () {
    $user = actingAsUser();
    $deck = Deck::factory()->create(['name' => 'Test Deck']);

    Livewire::test(Library::class)
        ->call('enrollInDeck', $deck->id)
        ->assertDispatched('deck-enrolled');
    
    expect($user->enrolledDecks()->where('deck_id', $deck->id)->exists())->toBeTrue();
});

test('user can unenroll from deck in library', function () {
    $user = actingAsUser();
    $deck = Deck::factory()->create(['name' => 'Test Deck']);
    
    // Enroll first
    $user->enrolledDecks()->attach($deck->id, ['enrolled_at' => now()]);

    Livewire::test(Library::class)
        ->call('unenrollFromDeck', $deck->id)
        ->assertDispatched('deck-unenrolled');
    
    expect($user->enrolledDecks()->where('deck_id', $deck->id)->exists())->toBeFalse();
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

test('library shows card count for decks', function () {
    $user = actingAsUser();
    $deck = Deck::factory()->create(['is_active' => true, 'name' => 'New Deck']);
    Card::factory()->count(5)->create(['deck_id' => $deck->id]);

    Livewire::test(Library::class)
        ->assertSee('New Deck')
        ->assertSee('5 cards');
});

test('library shows progress bar for enrolled decks', function () {
    $user = actingAsUser();
    $deck = Deck::factory()->create(['is_active' => true, 'name' => 'Complete Deck']);
    $cards = Card::factory()->count(3)->create(['deck_id' => $deck->id]);
    
    // Enroll user in deck
    $user->enrolledDecks()->attach($deck->id, ['enrolled_at' => now()]);

    // Review all cards
    foreach ($cards as $card) {
        CardReview::factory()->create([
            'user_id' => $user->id,
            'card_id' => $card->id,
        ]);
    }

    Livewire::test(Library::class)
        ->assertSee('Complete Deck')
        ->assertSee('100% complete');
});

test('library handles deck with no cards', function () {
    $user = actingAsUser();
    $deck = Deck::factory()->create(['is_active' => true, 'name' => 'Empty Deck']);
    // No cards in this deck

    Livewire::test(Library::class)
        ->assertSee('Empty Deck')
        ->assertSee('0 cards');
});

test('library shows correct card count for deck', function () {
    $user = actingAsUser();
    $deck = Deck::factory()->create(['is_active' => true, 'name' => 'Counted Deck']);
    Card::factory()->count(15)->create(['deck_id' => $deck->id]);

    Livewire::test(Library::class)
        ->assertSee('Counted Deck')
        ->assertSee('15 cards');
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

test('library enrollment is user-specific', function () {
    $user1 = actingAsUser();
    $user2 = User::factory()->create();
    
    $deck = Deck::factory()->create(['is_active' => true, 'name' => 'Shared Deck']);
    Card::factory()->count(2)->create(['deck_id' => $deck->id]);

    // User 2 enrolls in deck
    $user2->enrolledDecks()->attach($deck->id, ['enrolled_at' => now()]);

    // User 1 should see "Add to My Library" button (not enrolled)
    Livewire::test(Library::class)
        ->assertSee('Shared Deck')
        ->assertSee('Add to My Library')
        ->assertDontSee('✓ Enrolled');
});

test('library shows add to library button for unenrolled decks', function () {
    $user = actingAsUser();
    $deck = Deck::factory()->create(['is_active' => true, 'name' => 'Link Deck']);
    Card::factory()->create(['deck_id' => $deck->id]);

    Livewire::test(Library::class)
        ->assertSee('Add to My Library')
        ->assertSee('Link Deck');
});

test('library handles very large number of cards in deck', function () {
    $user = actingAsUser();
    $deck = Deck::factory()->create(['is_active' => true, 'name' => 'Large Deck']);
    Card::factory()->count(500)->create(['deck_id' => $deck->id]);

    Livewire::test(Library::class)
        ->assertSee('Large Deck')
        ->assertSee('500 cards')
        ->assertStatus(200);
});
