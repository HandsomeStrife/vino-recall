<?php

declare(strict_types=1);

use App\Livewire\Admin\CardManagement;
use Domain\Card\Models\Card;
use Domain\Deck\Models\Deck;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('admin can view card management', function () {
    $admin = actingAsAdmin();

    Livewire::test(CardManagement::class)
        ->assertStatus(200)
        ->assertSee('Card Management');
});

test('admin can create a new card', function () {
    $admin = actingAsAdmin();
    $deck = Deck::factory()->create();

    Livewire::test(CardManagement::class)
        ->set('deck_id', $deck->id)
        ->set('question', 'Test Question')
        ->set('answer_choices', ['Option A', 'Option B', 'Option C', 'Option D'])
        ->set('correct_answer_indices', [1])
        ->set('answer', 'Option B')
        ->call('createCard');

    $card = Card::where('question', 'Test Question')->first();
    expect($card)->not->toBeNull();
    expect($card->answer)->toBe('Option B');
    expect($card->deck_id)->toBe($deck->id);
    expect($card->card_type)->toBe('multiple_choice');
});

test('admin can create card with image', function () {
    Storage::fake('public');
    $admin = actingAsAdmin();
    $deck = Deck::factory()->create();
    $image = UploadedFile::fake()->image('card.jpg');

    Livewire::test(CardManagement::class)
        ->set('deck_id', $deck->id)
        ->set('question', 'Test Question')
        ->set('answer_choices', ['Option A', 'Option B', 'Option C', 'Option D'])
        ->set('correct_answer_indices', [0])
        ->set('answer', 'Option A')
        ->set('image', $image)
        ->call('createCard');

    $card = Card::where('question', 'Test Question')->first();
    expect($card)->not->toBeNull();
    expect($card->image_path)->not->toBeNull();
    Storage::disk('public')->assertExists($card->image_path);
});

test('admin can update an existing card', function () {
    $admin = actingAsAdmin();
    $deck = Deck::factory()->create();
    $card = Card::factory()->create([
        'deck_id' => $deck->id,
        'question' => 'Original Question',
    ]);

    Livewire::test(CardManagement::class)
        ->call('editCard', $card->id)
        ->assertSet('question', 'Original Question')
        ->set('question', 'Updated Question')
        ->call('updateCard');

    $card->refresh();
    expect($card->question)->toBe('Updated Question');
});

test('admin can delete a card', function () {
    $admin = actingAsAdmin();
    $deck = Deck::factory()->create();
    $card = Card::factory()->create(['deck_id' => $deck->id]);

    Livewire::test(CardManagement::class)
        ->call('deleteCard', $card->id);

    expect(Card::find($card->id))->toBeNull();
});

test('admin can create card with multiple correct answers', function () {
    $admin = actingAsAdmin();
    $deck = Deck::factory()->create();

    Livewire::test(CardManagement::class)
        ->set('deck_id', $deck->id)
        ->set('question', 'Which are red grapes?')
        ->set('answer_choices', ['Chardonnay', 'Merlot', 'Riesling', 'Syrah'])
        ->set('correct_answer_indices', [1, 3])
        ->set('answer', 'Merlot, Syrah')
        ->call('createCard');

    $card = Card::where('question', 'Which are red grapes?')->first();
    expect($card)->not->toBeNull();
    expect(json_decode($card->correct_answer_indices, true))->toBe([1, 3]);
});
