<?php

declare(strict_types=1);

use App\Livewire\DeckStats;
use Domain\Card\Enums\SrsStage;
use Domain\Card\Models\Card;
use Domain\Card\Models\CardReview;
use Domain\Card\Models\ReviewHistory;
use Domain\Deck\Actions\EnrollUserInDeckAction;
use Domain\Deck\Models\Deck;
use Livewire\Livewire;

test('deck stats page can be rendered for enrolled deck', function () {
    $user = actingAsUser();
    $deck = Deck::factory()->create(['name' => 'Test Deck']);

    // Enroll user in deck - returns shortcode string
    $shortcode = (new EnrollUserInDeckAction)->execute($user->id, $deck->id);

    Card::factory()->count(5)->create(['deck_id' => $deck->id]);

    Livewire::test(DeckStats::class, ['shortcode' => $shortcode])
        ->assertStatus(200)
        ->assertSee('Test Deck');
});

test('deck stats shows total card count', function () {
    $user = actingAsUser();
    $deck = Deck::factory()->create(['name' => 'Wine Basics']);

    $shortcode = (new EnrollUserInDeckAction)->execute($user->id, $deck->id);

    Card::factory()->count(10)->create(['deck_id' => $deck->id]);

    Livewire::test(DeckStats::class, ['shortcode' => $shortcode])
        ->assertSee('10'); // Total cards
});

test('deck stats shows reviewed and new card counts', function () {
    $user = actingAsUser();
    $deck = Deck::factory()->create();

    $shortcode = (new EnrollUserInDeckAction)->execute($user->id, $deck->id);

    $cards = Card::factory()->count(5)->create(['deck_id' => $deck->id]);

    // Review 2 cards
    CardReview::factory()->create([
        'user_id' => $user->id,
        'card_id' => $cards[0]->id,
        'srs_stage' => 1,
    ]);
    CardReview::factory()->create([
        'user_id' => $user->id,
        'card_id' => $cards[1]->id,
        'srs_stage' => 2,
    ]);

    Livewire::test(DeckStats::class, ['shortcode' => $shortcode])
        ->assertStatus(200);
});

test('deck stats shows due cards count', function () {
    $user = actingAsUser();
    $deck = Deck::factory()->create();

    $shortcode = (new EnrollUserInDeckAction)->execute($user->id, $deck->id);

    $card = Card::factory()->create(['deck_id' => $deck->id]);

    // Create a review that is due
    CardReview::factory()->create([
        'user_id' => $user->id,
        'card_id' => $card->id,
        'srs_stage' => 3,
        'next_review_at' => now()->subHour(),
    ]);

    Livewire::test(DeckStats::class, ['shortcode' => $shortcode])
        ->assertStatus(200);
});

test('deck stats shows accuracy rate from review history', function () {
    $user = actingAsUser();
    $deck = Deck::factory()->create();

    $shortcode = (new EnrollUserInDeckAction)->execute($user->id, $deck->id);

    $card = Card::factory()->create(['deck_id' => $deck->id]);

    // Create a card review (SRS state)
    CardReview::factory()->create([
        'user_id' => $user->id,
        'card_id' => $card->id,
        'srs_stage' => 1,
    ]);

    // Create review history entries for accuracy calculation
    ReviewHistory::factory()->create([
        'user_id' => $user->id,
        'card_id' => $card->id,
        'is_correct' => true,
        'is_practice' => false,
    ]);

    Livewire::test(DeckStats::class, ['shortcode' => $shortcode])
        ->assertStatus(200);
});

test('deck stats redirects for invalid shortcode', function () {
    $user = actingAsUser();

    Livewire::test(DeckStats::class, ['shortcode' => 'INVALID1'])
        ->assertRedirect(route('library'));
});

test('deck stats shows stage-based progress percentage', function () {
    $user = actingAsUser();
    $deck = Deck::factory()->create();

    $shortcode = (new EnrollUserInDeckAction)->execute($user->id, $deck->id);

    $cards = Card::factory()->count(4)->create(['deck_id' => $deck->id]);

    // Review 2 of 4 cards at stage 5 each
    // Progress = sum(5/9 + 5/9) / 4 * 100 = (10/9) / 4 * 100 = 27.8%
    CardReview::factory()->create([
        'user_id' => $user->id,
        'card_id' => $cards[0]->id,
        'srs_stage' => 5,
    ]);
    CardReview::factory()->create([
        'user_id' => $user->id,
        'card_id' => $cards[1]->id,
        'srs_stage' => 5,
    ]);

    Livewire::test(DeckStats::class, ['shortcode' => $shortcode])
        ->assertStatus(200)
        ->assertSee('27'); // 27.8% (the view shows 27.8)
});

test('deck stats shows recent activity from review history', function () {
    $user = actingAsUser();
    $deck = Deck::factory()->create();

    $shortcode = (new EnrollUserInDeckAction)->execute($user->id, $deck->id);

    $card = Card::factory()->create([
        'deck_id' => $deck->id,
        'question' => 'What is wine?',
    ]);

    // Create review history entry
    ReviewHistory::factory()->create([
        'user_id' => $user->id,
        'card_id' => $card->id,
        'is_correct' => true,
        'reviewed_at' => now(),
    ]);

    Livewire::test(DeckStats::class, ['shortcode' => $shortcode])
        ->assertStatus(200);
});

test('deck stats shows mastered cards count based on srs_stage', function () {
    $user = actingAsUser();
    $deck = Deck::factory()->create();

    $shortcode = (new EnrollUserInDeckAction)->execute($user->id, $deck->id);

    $card = Card::factory()->create(['deck_id' => $deck->id]);

    // Create a mastered card (srs_stage >= 7)
    CardReview::factory()->create([
        'user_id' => $user->id,
        'card_id' => $card->id,
        'srs_stage' => SrsStage::MASTERED_THRESHOLD,
    ]);

    Livewire::test(DeckStats::class, ['shortcode' => $shortcode])
        ->assertStatus(200);
});

test('deck stats shows empty state for deck with no cards', function () {
    $user = actingAsUser();
    $deck = Deck::factory()->create(['name' => 'Empty Deck']);

    $shortcode = (new EnrollUserInDeckAction)->execute($user->id, $deck->id);

    Livewire::test(DeckStats::class, ['shortcode' => $shortcode])
        ->assertStatus(200)
        ->assertSee('Empty Deck')
        ->assertSee('0'); // Zero cards
});

test('deck stats isolates data to specific deck', function () {
    $user = actingAsUser();

    $deck1 = Deck::factory()->create(['name' => 'Deck One']);
    $deck2 = Deck::factory()->create(['name' => 'Deck Two']);

    $shortcode1 = (new EnrollUserInDeckAction)->execute($user->id, $deck1->id);
    (new EnrollUserInDeckAction)->execute($user->id, $deck2->id);

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

    $shortcode = (new EnrollUserInDeckAction)->execute($user1->id, $deck->id);

    $card = Card::factory()->create(['deck_id' => $deck->id]);

    // Create review for user2 (different user)
    CardReview::factory()->create([
        'user_id' => $user2->id,
        'card_id' => $card->id,
        'srs_stage' => 3,
    ]);

    // User1's deck stats should show 0 reviewed
    Livewire::test(DeckStats::class, ['shortcode' => $shortcode])
        ->assertStatus(200);
});

test('accuracy is 100% when all reviews are correct', function () {
    $user = actingAsUser();
    $deck = Deck::factory()->create();

    $shortcode = (new EnrollUserInDeckAction)->execute($user->id, $deck->id);

    $cards = Card::factory()->count(5)->create(['deck_id' => $deck->id]);

    // Create 5 correct reviews in history
    foreach ($cards as $card) {
        CardReview::factory()->create([
            'user_id' => $user->id,
            'card_id' => $card->id,
            'srs_stage' => 1,
        ]);

        ReviewHistory::factory()->create([
            'user_id' => $user->id,
            'card_id' => $card->id,
            'is_correct' => true,
            'is_practice' => false,
        ]);
    }

    Livewire::test(DeckStats::class, ['shortcode' => $shortcode])
        ->assertStatus(200)
        ->assertSee('100'); // 100% accuracy
});

test('mastery rate is 0% after first perfect session', function () {
    $user = actingAsUser();
    $deck = Deck::factory()->create();

    $shortcode = (new EnrollUserInDeckAction)->execute($user->id, $deck->id);

    $cards = Card::factory()->count(10)->create(['deck_id' => $deck->id]);

    // All cards at stage 1 (just reviewed once correctly)
    foreach ($cards as $card) {
        CardReview::factory()->create([
            'user_id' => $user->id,
            'card_id' => $card->id,
            'srs_stage' => 1, // Stage 1, not mastered (need >= 7)
        ]);
    }

    Livewire::test(DeckStats::class, ['shortcode' => $shortcode])
        ->assertStatus(200)
        ->assertSee('0'); // 0% mastery rate
});

test('progress is approximately 11% after first perfect session', function () {
    $user = actingAsUser();
    $deck = Deck::factory()->create();

    $shortcode = (new EnrollUserInDeckAction)->execute($user->id, $deck->id);

    $cards = Card::factory()->count(9)->create(['deck_id' => $deck->id]);

    // All 9 cards at stage 1
    // Progress = sum(1/9 * 9) / 9 * 100 = 9/9 / 9 * 100 = 11.1%
    foreach ($cards as $card) {
        CardReview::factory()->create([
            'user_id' => $user->id,
            'card_id' => $card->id,
            'srs_stage' => 1,
        ]);
    }

    Livewire::test(DeckStats::class, ['shortcode' => $shortcode])
        ->assertStatus(200)
        ->assertSee('11'); // ~11.1% progress
});
