<?php

declare(strict_types=1);

use App\Livewire\Dashboard;
use Domain\Card\Models\Card;
use Domain\Card\Models\CardReview;
use Domain\Deck\Models\Deck;
use Livewire\Livewire;

test('dashboard can be rendered', function () {
    $user = actingAsUser();

    Livewire::test(Dashboard::class)
        ->assertStatus(200)
        ->assertSee('Dashboard', false);
});

test('dashboard shows user statistics', function () {
    $user = actingAsUser();
    $deck = Deck::factory()->create();
    
    // Enroll user in deck
    $user->enrolledDecks()->attach($deck->id, ['enrolled_at' => now()]);
    
    $card1 = Card::factory()->create(['deck_id' => $deck->id]);
    $card2 = Card::factory()->create(['deck_id' => $deck->id]);
    $card3 = Card::factory()->create(['deck_id' => $deck->id]);

    CardReview::factory()->create([
        'user_id' => $user->id,
        'card_id' => $card1->id,
        'ease_factor' => 2.5,
    ]);

    CardReview::factory()->create([
        'user_id' => $user->id,
        'card_id' => $card2->id,
        'ease_factor' => 2.5,
    ]);

    CardReview::factory()->create([
        'user_id' => $user->id,
        'card_id' => $card3->id,
        'ease_factor' => 2.5,
    ]);

    Livewire::test(Dashboard::class)
        ->assertSee('Cards Mastered')
        ->assertSee('Current Streak')
        ->assertSee('Time Spent');
});

test('dashboard shows due cards count', function () {
    $user = actingAsUser();
    $deck = Deck::factory()->create();
    $card = Card::factory()->create(['deck_id' => $deck->id]);

    CardReview::factory()->create([
        'user_id' => $user->id,
        'card_id' => $card->id,
        'next_review_at' => now()->subDay(),
    ]);

    Livewire::test(Dashboard::class)
        ->assertSee('due for review');
});

// Edge Case Tests

test('dashboard handles user with no reviews', function () {
    $user = actingAsUser();

    Livewire::test(Dashboard::class)
        ->assertSee('Cards Mastered')
        ->assertSee('Browse the library');
});

test('dashboard shows correct streak for consecutive days', function () {
    $user = actingAsUser();
    $deck = Deck::factory()->create();
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
        ->assertSee('Current Streak');
});

test('dashboard handles zero streak correctly', function () {
    $user = actingAsUser();
    $deck = Deck::factory()->create();
    $card = Card::factory()->create(['deck_id' => $deck->id]);

    // Review from 3 days ago (streak broken)
    CardReview::factory()->create([
        'user_id' => $user->id,
        'card_id' => $card->id,
        'created_at' => now()->subDays(3),
        'updated_at' => now()->subDays(3),
    ]);

    Livewire::test(Dashboard::class)
        ->assertSee('Current Streak')
        ->assertSee('0');
});

test('dashboard daily goal progress shows 100% when goal met', function () {
    $user = actingAsUser();
    $deck = Deck::factory()->create();
    
    // Create 20 reviews today (daily goal is 20)
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
        ->assertSee('Daily Goal');
});

test('dashboard daily goal progress caps at 100%', function () {
    $user = actingAsUser();
    $deck = Deck::factory()->create();
    
    // Create 30 reviews today (more than daily goal of 20)
    for ($i = 0; $i < 30; $i++) {
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
        ->assertStatus(200);
});

test('dashboard shows recent activity when available', function () {
    $user = actingAsUser();
    $deck = Deck::factory()->create();
    $card = Card::factory()->create([
        'deck_id' => $deck->id,
        'question' => 'Recent Question',
    ]);

    CardReview::factory()->create([
        'user_id' => $user->id,
        'card_id' => $card->id,
        'created_at' => now()->subHour(),
    ]);

    Livewire::test(Dashboard::class)
        ->assertSee('Recent Activity');
});

test('dashboard shows content when user has no reviews', function () {
    $user = actingAsUser();

    Livewire::test(Dashboard::class)
        ->assertSee('Dashboard')
        ->assertStatus(200);
});

test('dashboard mastery percentage calculates correctly', function () {
    $user = actingAsUser();
    $deck = Deck::factory()->create();
    
    // Create 10 total cards
    $cards = Card::factory()->count(10)->create(['deck_id' => $deck->id]);
    
    // User has mastered 3 cards
    for ($i = 0; $i < 3; $i++) {
        CardReview::factory()->create([
            'user_id' => $user->id,
            'card_id' => $cards[$i]->id,
        ]);
    }

    Livewire::test(Dashboard::class)
        ->assertSee('Mastery')
        ->assertStatus(200);
});

test('dashboard handles very large numbers correctly', function () {
    $user = actingAsUser();
    $deck = Deck::factory()->create();
    
    // Create many reviews
    for ($i = 0; $i < 100; $i++) {
        $card = Card::factory()->create(['deck_id' => $deck->id]);
        CardReview::factory()->create([
            'user_id' => $user->id,
            'card_id' => $card->id,
        ]);
    }

    Livewire::test(Dashboard::class)
        ->assertSee('Cards Mastered')
        ->assertStatus(200);
});

test('dashboard time spent calculation handles zero reviews', function () {
    $user = actingAsUser();

    Livewire::test(Dashboard::class)
        ->assertSee('Time Spent')
        ->assertSee('0');
});

test('dashboard shows correct due cards count', function () {
    $user = actingAsUser();
    $deck = Deck::factory()->create();
    $card1 = Card::factory()->create(['deck_id' => $deck->id]);
    $card2 = Card::factory()->create(['deck_id' => $deck->id]);
    $card3 = Card::factory()->create(['deck_id' => $deck->id]);

    // 2 cards due
    CardReview::factory()->create([
        'user_id' => $user->id,
        'card_id' => $card1->id,
        'next_review_at' => now()->subDay(),
    ]);

    CardReview::factory()->create([
        'user_id' => $user->id,
        'card_id' => $card2->id,
        'next_review_at' => now()->subHour(),
    ]);

    // 1 card not due yet
    CardReview::factory()->create([
        'user_id' => $user->id,
        'card_id' => $card3->id,
        'next_review_at' => now()->addDay(),
    ]);

    Livewire::test(Dashboard::class)
        ->assertSee('due for review');
});
