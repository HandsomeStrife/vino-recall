<?php

declare(strict_types=1);

use App\Livewire\Dashboard;
use Domain\Card\Models\Card;
use Domain\Card\Models\CardReview;
use Domain\Deck\Actions\EnrollUserInDeckAction;
use Domain\Deck\Models\Deck;
use Livewire\Livewire;

test('dashboard can be rendered', function () {
    $user = actingAsUser();

    Livewire::test(Dashboard::class)
        ->assertStatus(200)
        ->assertSee('Welcome back');
});

test('dashboard shows user name in welcome message', function () {
    $user = actingAsUser();

    Livewire::test(Dashboard::class)
        ->assertSee('Welcome back, '.$user->name);
});

test('dashboard shows browse library for user with no enrolled decks', function () {
    $user = actingAsUser();

    Livewire::test(Dashboard::class)
        ->assertSee('Browse Library')
        ->assertSee('haven\'t enrolled in any decks yet', escape: false);
});

test('dashboard shows daily goal section when user has enrolled decks', function () {
    $user = actingAsUser();
    $deck = Deck::factory()->create();

    // Enroll user in deck using action (generates shortcode)
    (new EnrollUserInDeckAction)->execute($user->id, $deck->id);

    Card::factory()->count(3)->create(['deck_id' => $deck->id]);

    Livewire::test(Dashboard::class)
        ->assertSee('Daily Goal');
});

test('dashboard shows recent mistakes section when user has enrolled decks', function () {
    $user = actingAsUser();
    $deck = Deck::factory()->create();

    (new EnrollUserInDeckAction)->execute($user->id, $deck->id);

    Card::factory()->count(3)->create(['deck_id' => $deck->id]);

    Livewire::test(Dashboard::class)
        ->assertSee('Recent Mistakes');
});

test('dashboard shows todays focus section with enrolled decks', function () {
    $user = actingAsUser();
    $deck = Deck::factory()->create(['name' => 'Wine Basics']);

    (new EnrollUserInDeckAction)->execute($user->id, $deck->id);

    Card::factory()->count(3)->create(['deck_id' => $deck->id]);

    Livewire::test(Dashboard::class)
        ->assertSee("Today's Focus", escape: false)
        ->assertSee('Wine Basics');
});

test('dashboard shows streak when user has consecutive days', function () {
    $user = actingAsUser();
    $deck = Deck::factory()->create();

    (new EnrollUserInDeckAction)->execute($user->id, $deck->id);

    $card1 = Card::factory()->create(['deck_id' => $deck->id]);
    $card2 = Card::factory()->create(['deck_id' => $deck->id]);

    // Review yesterday
    CardReview::factory()->create([
        'user_id' => $user->id,
        'card_id' => $card1->id,
        'created_at' => now()->subDay(),
        'updated_at' => now()->subDay(),
    ]);

    // Review today
    CardReview::factory()->create([
        'user_id' => $user->id,
        'card_id' => $card2->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    Livewire::test(Dashboard::class)
        ->assertSee('streak');
});

test('dashboard daily goal shows progress', function () {
    $user = actingAsUser();
    $deck = Deck::factory()->create();

    (new EnrollUserInDeckAction)->execute($user->id, $deck->id);

    // Create 5 reviews today (out of 20 goal)
    for ($i = 0; $i < 5; $i++) {
        $card = Card::factory()->create(['deck_id' => $deck->id]);
        CardReview::factory()->create([
            'user_id' => $user->id,
            'card_id' => $card->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    Livewire::test(Dashboard::class)
        ->assertSee('Daily Goal')
        ->assertSee('15 more to go'); // 20 - 5 = 15
});

test('dashboard shows daily goal achieved when goal met', function () {
    $user = actingAsUser();
    $deck = Deck::factory()->create();

    (new EnrollUserInDeckAction)->execute($user->id, $deck->id);

    // Create 20 reviews today (meets goal)
    for ($i = 0; $i < 20; $i++) {
        $card = Card::factory()->create(['deck_id' => $deck->id]);
        CardReview::factory()->create([
            'user_id' => $user->id,
            'card_id' => $card->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    Livewire::test(Dashboard::class)
        ->assertSee('Daily goal achieved');
});

test('dashboard shows no recent mistakes message when none exist', function () {
    $user = actingAsUser();
    $deck = Deck::factory()->create();

    (new EnrollUserInDeckAction)->execute($user->id, $deck->id);

    Card::factory()->count(3)->create(['deck_id' => $deck->id]);

    Livewire::test(Dashboard::class)
        ->assertSee('No recent mistakes')
        ->assertSee('Keep up the great work');
});

test('dashboard shows recent mistakes when user has incorrect reviews', function () {
    $user = actingAsUser();
    $deck = Deck::factory()->create();

    (new EnrollUserInDeckAction)->execute($user->id, $deck->id);

    $card = Card::factory()->create([
        'deck_id' => $deck->id,
        'question' => 'Test Question About Wine',
    ]);

    CardReview::factory()->create([
        'user_id' => $user->id,
        'card_id' => $card->id,
        'is_correct' => false,
        'rating' => 'incorrect',
        'created_at' => now(),
    ]);

    Livewire::test(Dashboard::class)
        ->assertSee('Test Question About Wine');
});

test('dashboard shows available decks for enrollment', function () {
    $user = actingAsUser();

    // Create a deck user is NOT enrolled in
    $availableDeck = Deck::factory()->create([
        'name' => 'Advanced Wine Studies',
        'is_active' => true,
    ]);

    Livewire::test(Dashboard::class)
        ->assertSee('Advanced Wine Studies');
});

test('dashboard handles user with many reviews correctly', function () {
    $user = actingAsUser();
    $deck = Deck::factory()->create();

    (new EnrollUserInDeckAction)->execute($user->id, $deck->id);

    // Create many reviews
    for ($i = 0; $i < 50; $i++) {
        $card = Card::factory()->create(['deck_id' => $deck->id]);
        CardReview::factory()->create([
            'user_id' => $user->id,
            'card_id' => $card->id,
        ]);
    }

    Livewire::test(Dashboard::class)
        ->assertStatus(200)
        ->assertSee('Daily Goal');
});

test('dashboard shows due cards count on deck card', function () {
    $user = actingAsUser();
    $deck = Deck::factory()->create(['name' => 'Test Deck']);

    (new EnrollUserInDeckAction)->execute($user->id, $deck->id);

    $card = Card::factory()->create(['deck_id' => $deck->id]);

    // Create a review that is due
    CardReview::factory()->create([
        'user_id' => $user->id,
        'card_id' => $card->id,
        'next_review_at' => now()->subDay(),
        'is_practice' => false,
    ]);

    Livewire::test(Dashboard::class)
        ->assertSee('Test Deck')
        ->assertStatus(200);
});
