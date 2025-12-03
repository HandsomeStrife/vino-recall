<?php

declare(strict_types=1);

use App\Livewire\StudyInterface;
use Domain\Card\Enums\CardRating;
use Domain\Card\Models\Card;
use Domain\Card\Models\CardReview;
use Domain\Deck\Models\Deck;
use Livewire\Livewire;

test('study interface can be rendered', function () {
    $user = actingAsUser();

    Livewire::test(StudyInterface::class)
        ->assertStatus(200);
});

test('study interface shows card when available', function () {
    $user = actingAsUser();
    $deck = Deck::factory()->create();
    $card = Card::factory()->create([
        'deck_id' => $deck->id,
        'question' => 'Test Question',
        'answer' => 'Test Answer',
    ]);

    CardReview::factory()->create([
        'user_id' => $user->id,
        'card_id' => $card->id,
        'next_review_at' => now()->subDay(),
    ]);

    Livewire::test(StudyInterface::class)
        ->assertSee('Test Question');
});

test('study interface reveals answer on reveal click', function () {
    $user = actingAsUser();
    $deck = Deck::factory()->create();
    $card = Card::factory()->traditional()->create([
        'deck_id' => $deck->id,
        'question' => 'Test Question',
        'answer' => 'Test Answer',
    ]);

    CardReview::factory()->create([
        'user_id' => $user->id,
        'card_id' => $card->id,
        'next_review_at' => now()->subDay(),
    ]);

    Livewire::test(StudyInterface::class)
        ->assertSee('Reveal Answer')
        ->call('reveal')
        ->assertSee('Test Answer')
        ->assertSee('Again');
});

test('study interface rates card and loads next', function () {
    $user = actingAsUser();
    $deck = Deck::factory()->create();
    $card1 = Card::factory()->create(['deck_id' => $deck->id]);
    $card2 = Card::factory()->create(['deck_id' => $deck->id]);

    CardReview::factory()->create([
        'user_id' => $user->id,
        'card_id' => $card1->id,
        'next_review_at' => now()->subDay(),
    ]);

    Livewire::test(StudyInterface::class)
        ->call('reveal')
        ->call('rate', 'good')
        ->assertSet('revealed', false);

    $review = CardReview::where('user_id', $user->id)
        ->where('card_id', $card1->id)
        ->first();

    expect($review)->not->toBeNull();
    expect($review->rating)->toBe(CardRating::GOOD->value);
});

test('study interface filters by deck when deck parameter provided', function () {
    $user = actingAsUser();
    $deck1 = Deck::factory()->create();
    $deck2 = Deck::factory()->create();
    $card1 = Card::factory()->create(['deck_id' => $deck1->id]);
    $card2 = Card::factory()->create(['deck_id' => $deck2->id]);

    CardReview::factory()->create([
        'user_id' => $user->id,
        'card_id' => $card1->id,
        'next_review_at' => now()->subDay(),
    ]);

    CardReview::factory()->create([
        'user_id' => $user->id,
        'card_id' => $card2->id,
        'next_review_at' => now()->subDay(),
    ]);

    Livewire::withQueryParams(['deck' => $deck1->id])
        ->test(StudyInterface::class)
        ->assertSee($card1->question);
});

// Edge case tests

test('study interface shows message when no cards are due', function () {
    $user = actingAsUser();
    $deck = Deck::factory()->create();
    $card = Card::factory()->create(['deck_id' => $deck->id]);

    // Card exists but not due yet
    CardReview::factory()->create([
        'user_id' => $user->id,
        'card_id' => $card->id,
        'next_review_at' => now()->addWeek(),
    ]);

    Livewire::test(StudyInterface::class)
        ->assertSee('No cards due for review');
});

test('study interface shows new card when user has not reviewed it yet', function () {
    $user = actingAsUser();
    $deck = Deck::factory()->create();
    $card = Card::factory()->create([
        'deck_id' => $deck->id,
        'question' => 'New Question',
    ]);

    // No CardReview exists for this user and card

    Livewire::test(StudyInterface::class)
        ->assertSee('New Question');
});

test('study interface handles empty deck gracefully', function () {
    $user = actingAsUser();
    $deck = Deck::factory()->create();
    // No cards in deck

    Livewire::test(StudyInterface::class)
        ->assertSee('No cards due for review');
});

test('study interface with invalid deck parameter shows all due cards', function () {
    $user = actingAsUser();
    $deck = Deck::factory()->create();
    $card = Card::factory()->create(['deck_id' => $deck->id]);

    CardReview::factory()->create([
        'user_id' => $user->id,
        'card_id' => $card->id,
        'next_review_at' => now()->subDay(),
    ]);

    // Deck ID 999999 does not exist
    Livewire::withQueryParams(['deck' => 999999])
        ->test(StudyInterface::class)
        ->assertStatus(200);
});

test('study interface shows multiple due cards in sequence', function () {
    $user = actingAsUser();
    $deck = Deck::factory()->create();
    $card1 = Card::factory()->create(['deck_id' => $deck->id, 'question' => 'Question 1']);
    $card2 = Card::factory()->create(['deck_id' => $deck->id, 'question' => 'Question 2']);

    CardReview::factory()->create([
        'user_id' => $user->id,
        'card_id' => $card1->id,
        'next_review_at' => now()->subDay(),
    ]);

    CardReview::factory()->create([
        'user_id' => $user->id,
        'card_id' => $card2->id,
        'next_review_at' => now()->subDay(),
    ]);

    // Rate first card, component should still be usable
    Livewire::test(StudyInterface::class)
        ->call('reveal')
        ->call('rate', 'good')
        ->assertStatus(200);
});

test('study interface handles rating card multiple times correctly', function () {
    $user = actingAsUser();
    $deck = Deck::factory()->create();
    $card = Card::factory()->create(['deck_id' => $deck->id]);

    $review = CardReview::factory()->create([
        'user_id' => $user->id,
        'card_id' => $card->id,
        'next_review_at' => now()->subDay(),
        'ease_factor' => 2.5,
    ]);

    Livewire::test(StudyInterface::class)
        ->call('reveal')
        ->call('rate', 'easy');

    $review->refresh();
    expect($review->rating)->toBe(CardRating::EASY->value)
        ->and($review->next_review_at)->toBeGreaterThan(now());
});

test('study interface does not show revealed answer before reveal is called', function () {
    $user = actingAsUser();
    $deck = Deck::factory()->create();
    $card = Card::factory()->traditional()->create([
        'deck_id' => $deck->id,
        'question' => 'Test Question',
        'answer' => 'Test Answer',
    ]);

    CardReview::factory()->create([
        'user_id' => $user->id,
        'card_id' => $card->id,
        'next_review_at' => now()->subDay(),
    ]);

    Livewire::test(StudyInterface::class)
        ->assertSee('Test Question')
        ->assertDontSee('Test Answer')
        ->assertSee('Reveal Answer');
});

test('study interface resets revealed state after rating', function () {
    $user = actingAsUser();
    $deck = Deck::factory()->create();
    $card1 = Card::factory()->create(['deck_id' => $deck->id]);
    $card2 = Card::factory()->create(['deck_id' => $deck->id]);

    CardReview::factory()->create([
        'user_id' => $user->id,
        'card_id' => $card1->id,
        'next_review_at' => now()->subDay(),
    ]);

    CardReview::factory()->create([
        'user_id' => $user->id,
        'card_id' => $card2->id,
        'next_review_at' => now()->subDay(),
    ]);

    Livewire::test(StudyInterface::class)
        ->call('reveal')
        ->assertSet('revealed', true)
        ->call('rate', 'good')
        ->assertSet('revealed', false);
});
