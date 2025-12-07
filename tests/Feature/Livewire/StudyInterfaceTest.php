<?php

declare(strict_types=1);

use App\Livewire\StudyInterface;
use Domain\Card\Models\Card;
use Domain\Card\Models\CardReview;
use Domain\Deck\Models\Deck;
use Domain\User\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    // StudyInterface requires a deck shortcode
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
    
    $this->deck = Deck::factory()->create();
    
    // Enroll user in deck
    $this->user->enrolledDecks()->attach($this->deck->id, [
        'shortcode' => 'TESTCODE',
        'enrolled_at' => now(),
    ]);
});

test('study interface shows card when available', function () {
    $card = Card::factory()->singleCorrectAnswer()->create([
        'deck_id' => $this->deck->id,
        'question' => 'Test Question',
        'answer' => 'Test Answer',
    ]);

    Livewire::test(StudyInterface::class, ['type' => 'deep_study', 'deck' => 'TESTCODE'])
        ->assertSee('Test Question');
});

test('study interface shows select all that apply for multi-answer cards', function () {
    $card = Card::factory()->multipleCorrectAnswers()->create([
        'deck_id' => $this->deck->id,
        'question' => 'Multi Answer Question',
    ]);

    Livewire::test(StudyInterface::class, ['type' => 'deep_study', 'deck' => 'TESTCODE'])
        ->assertSee('Multi Answer Question')
        ->assertSee('Select all that apply');
});

test('study interface can submit answers and reveal result', function () {
    $card = Card::factory()->create([
        'deck_id' => $this->deck->id,
        'question' => 'Test Question',
        'answer' => 'Correct Answer',
        'answer_choices' => json_encode(['Wrong', 'Correct Answer', 'Also Wrong', 'Nope']),
        'correct_answer_indices' => json_encode([1]),
    ]);

    // submitAnswers now accepts an array of answers from Alpine
    Livewire::test(StudyInterface::class, ['type' => 'deep_study', 'deck' => 'TESTCODE'])
        ->assertSet('revealed', false)
        ->call('submitAnswers', ['Correct Answer'])
        ->assertSet('revealed', true)
        ->assertSet('selectedAnswers', ['Correct Answer']);
});

test('study interface continues to next card after review', function () {
    $card1 = Card::factory()->singleCorrectAnswer()->create([
        'deck_id' => $this->deck->id,
        'question' => 'Question 1',
    ]);
    $card2 = Card::factory()->singleCorrectAnswer()->create([
        'deck_id' => $this->deck->id,
        'question' => 'Question 2',
    ]);

    $component = Livewire::test(StudyInterface::class, ['type' => 'deep_study', 'deck' => 'TESTCODE'])
        ->assertSee('Question 1')
        ->call('submitAnswers', ['Option B'])
        ->call('continue')
        ->assertSet('revealed', false)
        ->assertSet('selectedAnswers', []);
        
    // Should now be on next card
    expect($component->get('currentCardIndex'))->toBe(1);
});

test('study interface shows completion message when all cards done', function () {
    $card = Card::factory()->singleCorrectAnswer()->create([
        'deck_id' => $this->deck->id,
        'question' => 'Only Question',
    ]);

    Livewire::test(StudyInterface::class, ['type' => 'deep_study', 'deck' => 'TESTCODE'])
        ->call('submitAnswers', ['Option B'])
        ->call('continue')
        ->assertSee('Deep Study Complete');
});

test('study interface does not submit when no answer selected', function () {
    $card = Card::factory()->singleCorrectAnswer()->create([
        'deck_id' => $this->deck->id,
        'question' => 'Test Question',
    ]);

    Livewire::test(StudyInterface::class, ['type' => 'deep_study', 'deck' => 'TESTCODE'])
        ->assertSet('selectedAnswers', [])
        ->call('submitAnswers', [])
        // Should not reveal because no answers selected
        ->assertSet('revealed', false);
});

test('study interface creates review record after submission', function () {
    $card = Card::factory()->create([
        'deck_id' => $this->deck->id,
        'question' => 'Test Question',
        'answer' => 'Correct',
        'answer_choices' => json_encode(['Wrong', 'Correct', 'Also Wrong', 'Nope']),
        'correct_answer_indices' => json_encode([1]),
    ]);

    Livewire::test(StudyInterface::class, ['type' => 'deep_study', 'deck' => 'TESTCODE'])
        ->call('submitAnswers', ['Correct'])
        ->call('continue');

    $this->assertDatabaseHas('card_reviews', [
        'user_id' => $this->user->id,
        'card_id' => $card->id,
        'is_correct' => true,
    ]);
});

test('study interface records incorrect answer', function () {
    $card = Card::factory()->create([
        'deck_id' => $this->deck->id,
        'question' => 'Test Question',
        'answer' => 'Correct',
        'answer_choices' => json_encode(['Wrong', 'Correct', 'Also Wrong', 'Nope']),
        'correct_answer_indices' => json_encode([1]),
    ]);

    Livewire::test(StudyInterface::class, ['type' => 'deep_study', 'deck' => 'TESTCODE'])
        ->call('submitAnswers', ['Wrong'])
        ->call('continue');

    $this->assertDatabaseHas('card_reviews', [
        'user_id' => $this->user->id,
        'card_id' => $card->id,
        'is_correct' => false,
    ]);
});

test('practice session does not create new SRS review if one exists', function () {
    $card = Card::factory()->create([
        'deck_id' => $this->deck->id,
        'question' => 'Test Question',
        'answer' => 'Correct',
        'answer_choices' => json_encode(['Wrong', 'Correct']),
        'correct_answer_indices' => json_encode([1]),
    ]);
    
    // Create existing SRS review
    CardReview::factory()->create([
        'user_id' => $this->user->id,
        'card_id' => $card->id,
        'is_practice' => false,
        'ease_factor' => 2.5,
        'next_review_at' => now()->addDay(),
    ]);

    Livewire::test(StudyInterface::class, ['type' => 'practice', 'deck' => 'TESTCODE'])
        ->call('submitAnswers', ['Wrong'])
        ->call('continue');

    // Should only have 1 review (the original)
    expect(CardReview::where('user_id', $this->user->id)->where('card_id', $card->id)->count())->toBe(1);
});

test('study interface offers to load more cards after normal session completes', function () {
    // Create 2 cards (less than default batch size)
    $card1 = Card::factory()->singleCorrectAnswer()->create([
        'deck_id' => $this->deck->id,
        'question' => 'Question 1',
    ]);
    $card2 = Card::factory()->singleCorrectAnswer()->create([
        'deck_id' => $this->deck->id,
        'question' => 'Question 2',
    ]);

    // Complete both cards
    $component = Livewire::test(StudyInterface::class, ['type' => 'normal', 'deck' => 'TESTCODE'])
        ->call('submitAnswers', ['Option B'])
        ->call('continue')
        ->call('submitAnswers', ['Option B'])
        ->call('continue');
        
    // Session should be complete
    expect($component->get('sessionComplete'))->toBeTrue();
});
