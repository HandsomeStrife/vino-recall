<?php

declare(strict_types=1);

use App\Livewire\Admin\DeckManagement;
use Domain\Category\Models\Category;
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
        ->call('openEditModal', $deck->id)
        ->assertSet('name', 'Original Name')
        ->assertSet('showModal', true)
        ->set('name', 'Updated Name')
        ->call('updateDeck')
        ->assertSet('showModal', false);

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

test('admin can assign categories to deck on creation', function () {
    $admin = actingAsAdmin();
    $category1 = Category::factory()->create();
    $category2 = Category::factory()->create();

    Livewire::test(DeckManagement::class)
        ->set('name', 'Categorized Deck')
        ->set('description', 'A deck with categories')
        ->set('selectedCategories', [$category1->id, $category2->id])
        ->call('createDeck')
        ->assertHasNoErrors();

    $deck = Deck::where('name', 'Categorized Deck')->first();
    expect($deck)->not->toBeNull();
    expect($deck->categories()->count())->toBe(2);
    expect($deck->categories->pluck('id')->toArray())->toContain($category1->id, $category2->id);
});

test('admin can update deck categories', function () {
    $admin = actingAsAdmin();
    $deck = Deck::factory()->create();
    $oldCategory = Category::factory()->create();
    $newCategory = Category::factory()->create();
    
    $deck->categories()->attach($oldCategory->id);

    Livewire::test(DeckManagement::class)
        ->call('openEditModal', $deck->id)
        ->set('selectedCategories', [$newCategory->id])
        ->call('updateDeck')
        ->assertHasNoErrors();

    $deck->refresh();
    expect($deck->categories()->count())->toBe(1);
    expect($deck->categories->first()->id)->toBe($newCategory->id);
});

test('deck can have multiple categories', function () {
    $admin = actingAsAdmin();
    $categories = Category::factory()->count(5)->create();

    Livewire::test(DeckManagement::class)
        ->set('name', 'Multi-Category Deck')
        ->set('selectedCategories', $categories->pluck('id')->toArray())
        ->call('createDeck');

    $deck = Deck::where('name', 'Multi-Category Deck')->first();
    expect($deck->categories()->count())->toBe(5);
});

test('admin can remove all categories from deck', function () {
    $admin = actingAsAdmin();
    $deck = Deck::factory()->create();
    $categories = Category::factory()->count(2)->create();
    $deck->categories()->attach($categories->pluck('id'));

    Livewire::test(DeckManagement::class)
        ->call('openEditModal', $deck->id)
        ->set('selectedCategories', [])
        ->call('updateDeck');

    $deck->refresh();
    expect($deck->categories()->count())->toBe(0);
});
