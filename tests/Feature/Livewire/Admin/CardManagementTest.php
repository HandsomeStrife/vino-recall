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
        ->set('answer', 'Test Answer')
        ->call('createCard');

    $card = Card::where('question', 'Test Question')->first();
    expect($card)->not->toBeNull();
    expect($card->answer)->toBe('Test Answer');
    expect($card->deck_id)->toBe($deck->id);
});

test('admin can create card with image', function () {
    Storage::fake('public');
    $admin = actingAsAdmin();
    $deck = Deck::factory()->create();
    $image = UploadedFile::fake()->image('card.jpg');

    Livewire::test(CardManagement::class)
        ->set('deck_id', $deck->id)
        ->set('question', 'Test Question')
        ->set('answer', 'Test Answer')
        ->set('image', $image)
        ->call('createCard');

    $card = Card::where('question', 'Test Question')->first();
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
