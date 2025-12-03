<?php

declare(strict_types=1);

use App\Livewire\Admin\DeckManagement;
use Domain\Deck\Models\Deck;
use Livewire\Livewire;

test('admin can view deck management', function () {
    $admin = actingAsAdmin();

    Livewire::test(DeckManagement::class)
        ->assertStatus(200)
        ->assertSee('Deck Management');
});

test('admin can create a new deck', function () {
    $admin = actingAsAdmin();

    Livewire::test(DeckManagement::class)
        ->set('name', 'New Deck')
        ->set('description', 'Deck Description')
        ->set('is_active', true)
        ->call('createDeck')
        ->assertSet('name', '');

    $deck = Deck::where('name', 'New Deck')->first();
    expect($deck)->not->toBeNull();
    expect($deck->description)->toBe('Deck Description');
});

test('admin can update an existing deck', function () {
    $admin = actingAsAdmin();
    $deck = Deck::factory()->create(['name' => 'Original Name']);

    Livewire::test(DeckManagement::class)
        ->call('editDeck', $deck->id)
        ->assertSet('name', 'Original Name')
        ->set('name', 'Updated Name')
        ->call('updateDeck')
        ->assertSet('editingDeckId', null);

    $deck->refresh();
    expect($deck->name)->toBe('Updated Name');
});

test('admin can delete a deck', function () {
    $admin = actingAsAdmin();
    $deck = Deck::factory()->create();

    Livewire::test(DeckManagement::class)
        ->call('deleteDeck', $deck->id);

    expect(Deck::find($deck->id))->toBeNull();
});
