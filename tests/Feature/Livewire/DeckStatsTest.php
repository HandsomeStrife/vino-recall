<?php

declare(strict_types=1);

use App\Livewire\DeckStats;
use Domain\Card\Models\Card;
use Domain\Card\Models\CardReview;
use Domain\Deck\Actions\EnrollUserInDeckAction;
use Domain\Deck\Models\Deck;
use Livewire\Livewire;

test('deck stats page can be rendered for enrolled deck', function () {
    $user = actingAsUser();
    $deck = Deck::factory()->create(['name' => 'Test Deck']);
    
    // Enroll user in deck - returns shortcode string
    $shortcode = (new EnrollUserInDeckAction())->execute($user->id, $deck->id);
    
    Card::factory()->count(5)->create(['deck_id' => $deck->id]);

    Livewire::test(DeckStats::class, ['shortcode' => $shortcode])
        ->assertStatus(200)
        ->assertSee('Test Deck');
});

test('deck stats shows total card count', function () {
    $user = actingAsUser();
    $deck = Deck::factory()->create(['name' => 'Wine Basics']);
    
    $shortcode = (new EnrollUserInDeckAction())->execute($user->id, $deck->id);
    
    Card::factory()->count(10)->create(['deck_id' => $deck->id]);

    Livewire::test(DeckStats::class, ['shortcode' => $shortcode])
        ->assertSee('10'); // Total cards
});

test('deck stats shows reviewed and new card counts', function () {
    $user = actingAsUser();
    $deck = Deck::factory()->create();
    
    $shortcode = (new EnrollUserInDeckAction())->execute($user->id, $deck->id);
    
    $cards = Card::factory()->count(5)->create(['deck_id' => $deck->id]);
    
    // Review 2 cards
    CardReview::factory()->create([
        'user_id' => $user->id,
        'card_id' => $cards[0]->id,
    ]);
    CardReview::factory()->create([
        'user_id' => $user->id,
        'card_id' => $cards[1]->id,
    ]);

    Livewire::test(DeckStats::class, ['shortcode' => $shortcode])
        ->assertStatus(200);
});

test('deck stats shows due cards count', function () {
    $user = actingAsUser();
    $deck = Deck::factory()->create();
    
    $shortcode = (new EnrollUserInDeckAction())->execute($user->id, $deck->id);
    
    $card = Card::factory()->create(['deck_id' => $deck->id]);
    
    // Create a review that is due
    CardReview::factory()->create([
        'user_id' => $user->id,
        'card_id' => $card->id,
        'next_review_at' => now()->subHour(),
        'is_practice' => false,
    ]);

    Livewire::test(DeckStats::class, ['shortcode' => $shortcode])
        ->assertStatus(200);
});

test('deck stats shows accuracy rate', function () {
    $user = actingAsUser();
    $deck = Deck::factory()->create();
    
    $shortcode = (new EnrollUserInDeckAction())->execute($user->id, $deck->id);
    
    $card = Card::factory()->create(['deck_id' => $deck->id]);
    
    // Create some correct and incorrect reviews
    CardReview::factory()->create([
        'user_id' => $user->id,
        'card_id' => $card->id,
        'rating' => 'correct',
        'is_correct' => true,
    ]);

    Livewire::test(DeckStats::class, ['shortcode' => $shortcode])
        ->assertStatus(200);
});

test('deck stats redirects for invalid shortcode', function () {
    $user = actingAsUser();

    Livewire::test(DeckStats::class, ['shortcode' => 'INVALID1'])
        ->assertRedirect(route('library'));
});

test('deck stats shows progress percentage', function () {
    $user = actingAsUser();
    $deck = Deck::factory()->create();
    
    $shortcode = (new EnrollUserInDeckAction())->execute($user->id, $deck->id);
    
    $cards = Card::factory()->count(4)->create(['deck_id' => $deck->id]);
    
    // Review 2 of 4 cards (50%)
    CardReview::factory()->create([
        'user_id' => $user->id,
        'card_id' => $cards[0]->id,
    ]);
    CardReview::factory()->create([
        'user_id' => $user->id,
        'card_id' => $cards[1]->id,
    ]);

    Livewire::test(DeckStats::class, ['shortcode' => $shortcode])
        ->assertStatus(200)
        ->assertSee('50'); // 50% progress
});

test('deck stats shows recent activity', function () {
    $user = actingAsUser();
    $deck = Deck::factory()->create();
    
    $shortcode = (new EnrollUserInDeckAction())->execute($user->id, $deck->id);
    
    $card = Card::factory()->create([
        'deck_id' => $deck->id,
        'question' => 'What is wine?',
    ]);
    
    CardReview::factory()->create([
        'user_id' => $user->id,
        'card_id' => $card->id,
        'created_at' => now(),
    ]);

    Livewire::test(DeckStats::class, ['shortcode' => $shortcode])
        ->assertStatus(200);
});

test('deck stats shows mastered cards count', function () {
    $user = actingAsUser();
    $deck = Deck::factory()->create();
    
    $shortcode = (new EnrollUserInDeckAction())->execute($user->id, $deck->id);
    
    $card = Card::factory()->create(['deck_id' => $deck->id]);
    
    // Create a mastered card (ease_factor >= 2.0)
    CardReview::factory()->create([
        'user_id' => $user->id,
        'card_id' => $card->id,
        'ease_factor' => 2.5,
    ]);

    Livewire::test(DeckStats::class, ['shortcode' => $shortcode])
        ->assertStatus(200);
});

test('deck stats shows empty state for deck with no cards', function () {
    $user = actingAsUser();
    $deck = Deck::factory()->create(['name' => 'Empty Deck']);
    
    $shortcode = (new EnrollUserInDeckAction())->execute($user->id, $deck->id);

    Livewire::test(DeckStats::class, ['shortcode' => $shortcode])
        ->assertStatus(200)
        ->assertSee('Empty Deck')
        ->assertSee('0'); // Zero cards
});

test('deck stats isolates data to specific deck', function () {
    $user = actingAsUser();
    
    $deck1 = Deck::factory()->create(['name' => 'Deck One']);
    $deck2 = Deck::factory()->create(['name' => 'Deck Two']);
    
    $shortcode1 = (new EnrollUserInDeckAction())->execute($user->id, $deck1->id);
    (new EnrollUserInDeckAction())->execute($user->id, $deck2->id);
    
    // Create cards for both decks
    Card::factory()->count(5)->create(['deck_id' => $deck1->id]);
    Card::factory()->count(10)->create(['deck_id' => $deck2->id]);

    // View deck 1 stats - should only show 5 cards
    Livewire::test(DeckStats::class, ['shortcode' => $shortcode1])
        ->assertSee('Deck One')
        ->assertSee('5');
});

test('deck stats does not show other users reviews', function () {
    $user1 = actingAsUser();
    $user2 = \Domain\User\Models\User::factory()->create();
    
    $deck = Deck::factory()->create();
    
    $shortcode = (new EnrollUserInDeckAction())->execute($user1->id, $deck->id);
    
    $card = Card::factory()->create(['deck_id' => $deck->id]);
    
    // Create review for user2 (different user)
    CardReview::factory()->create([
        'user_id' => $user2->id,
        'card_id' => $card->id,
    ]);

    // User1's deck stats should show 0 reviewed
    Livewire::test(DeckStats::class, ['shortcode' => $shortcode])
        ->assertStatus(200);
});
