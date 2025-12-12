<?php

declare(strict_types=1);

use App\Livewire\CollectionDetail;
use App\Livewire\Library;
use Domain\Deck\Models\Deck;
use Domain\User\Models\User;
use Livewire\Livewire;

test('can view collection without enrolling', function () {
    $user = actingAsUser();
    $collection = Deck::factory()->create([
        'is_collection' => true,
        'name' => 'Test Collection',
    ]);

    Livewire::test(CollectionDetail::class, ['collectionId' => $collection->id])
        ->assertStatus(200)
        ->assertSee('Test Collection')
        ->assertSee('Enroll in Entire Collection');
});

test('collection detail shows enroll buttons when not enrolled', function () {
    $user = actingAsUser();
    $collection = Deck::factory()->create([
        'is_collection' => true,
        'name' => 'Wine Basics',
    ]);

    $childDeck = Deck::factory()->create([
        'parent_deck_id' => $collection->id,
        'name' => 'Grape Types',
    ]);

    Livewire::test(CollectionDetail::class, ['collectionId' => $collection->id])
        ->assertSee('Enroll in Entire Collection')
        ->assertSee('Add to My Decks');
});

test('can enroll in individual child deck', function () {
    $user = actingAsUser();
    $collection = Deck::factory()->create([
        'is_collection' => true,
    ]);

    $childDeck = Deck::factory()->create([
        'parent_deck_id' => $collection->id,
        'name' => 'Red Wines',
    ]);

    Livewire::test(CollectionDetail::class, ['collectionId' => $collection->id])
        ->call('enrollInChildDeck', $childDeck->id)
        ->assertDispatched('deck-enrolled');

    expect($user->enrolledDecks()->where('deck_id', $childDeck->id)->exists())->toBeTrue();
    expect($user->enrolledDecks()->where('deck_id', $collection->id)->exists())->toBeFalse();
});

test('individual deck appears in my decks', function () {
    $user = actingAsUser();
    $collection = Deck::factory()->create([
        'is_collection' => true,
        'name' => 'WSET Level 2',
    ]);

    $childDeck = Deck::factory()->create([
        'parent_deck_id' => $collection->id,
        'name' => 'Bordeaux Basics',
    ]);

    // Enroll in child deck only
    $user->enrolledDecks()->attach($childDeck->id, [
        'enrolled_at' => now(),
        'shortcode' => 'child001',
    ]);

    Livewire::test(Library::class)
        ->call('switchTab', 'enrolled')
        ->assertSee('Bordeaux Basics')
        ->assertSee('My Decks');
});

test('individual deck shows parent collection name', function () {
    $user = actingAsUser();
    $collection = Deck::factory()->create([
        'is_collection' => true,
        'name' => 'WSET Level 2',
    ]);

    $childDeck = Deck::factory()->create([
        'parent_deck_id' => $collection->id,
        'name' => 'Bordeaux Basics',
    ]);

    // Enroll in child deck only
    $user->enrolledDecks()->attach($childDeck->id, [
        'enrolled_at' => now(),
        'shortcode' => 'child002',
    ]);

    Livewire::test(Library::class)
        ->call('switchTab', 'enrolled')
        ->assertSee('Bordeaux Basics')
        ->assertSee('From: WSET Level 2');
});

test('enrolling in collection keeps individual enrollments', function () {
    $user = actingAsUser();
    $collection = Deck::factory()->create([
        'is_collection' => true,
        'name' => 'Wine Collection',
    ]);

    $childDeck1 = Deck::factory()->create([
        'parent_deck_id' => $collection->id,
        'name' => 'Child 1',
    ]);

    $childDeck2 = Deck::factory()->create([
        'parent_deck_id' => $collection->id,
        'name' => 'Child 2',
    ]);

    // First enroll in one child deck individually
    $user->enrolledDecks()->attach($childDeck1->id, [
        'enrolled_at' => now(),
        'shortcode' => 'child1a1',
    ]);

    // Then enroll in the entire collection
    Livewire::test(CollectionDetail::class, ['collectionId' => $collection->id])
        ->call('enrollInCollection')
        ->assertDispatched('collection-enrolled');

    // Both child decks should be enrolled
    expect($user->enrolledDecks()->where('deck_id', $childDeck1->id)->exists())->toBeTrue();
    expect($user->enrolledDecks()->where('deck_id', $childDeck2->id)->exists())->toBeTrue();
    expect($user->enrolledDecks()->where('deck_id', $collection->id)->exists())->toBeTrue();
});

test('collection appears in my collections', function () {
    $user = actingAsUser();
    $collection = Deck::factory()->create([
        'is_collection' => true,
        'name' => 'WSET Level 1',
    ]);

    $childDeck = Deck::factory()->create([
        'parent_deck_id' => $collection->id,
    ]);

    // Enroll in collection
    $user->enrolledDecks()->attach($collection->id, [
        'enrolled_at' => now(),
        'shortcode' => 'collect1',
    ]);
    $user->enrolledDecks()->attach($childDeck->id, [
        'enrolled_at' => now(),
        'shortcode' => 'child003',
    ]);

    Livewire::test(Library::class)
        ->call('switchTab', 'enrolled')
        ->assertSee('WSET Level 1')
        ->assertSee('My Collections');
});

test('unenrolling from collection removes all decks', function () {
    $user = actingAsUser();
    $collection = Deck::factory()->create([
        'is_collection' => true,
    ]);

    $childDeck1 = Deck::factory()->create(['parent_deck_id' => $collection->id]);
    $childDeck2 = Deck::factory()->create(['parent_deck_id' => $collection->id]);

    // Enroll in collection and children
    $user->enrolledDecks()->attach($collection->id, [
        'enrolled_at' => now(),
        'shortcode' => 'collect2',
    ]);
    $user->enrolledDecks()->attach($childDeck1->id, [
        'enrolled_at' => now(),
        'shortcode' => 'child1b1',
    ]);
    $user->enrolledDecks()->attach($childDeck2->id, [
        'enrolled_at' => now(),
        'shortcode' => 'child2b1',
    ]);

    // Unenroll from collection
    Livewire::test(Library::class)
        ->call('unenrollFromDeck', $collection->id)
        ->assertDispatched('deck-unenrolled');

    // All enrollments should be removed
    expect($user->fresh()->enrolledDecks()->where('deck_id', $collection->id)->exists())->toBeFalse();
    expect($user->fresh()->enrolledDecks()->where('deck_id', $childDeck1->id)->exists())->toBeFalse();
    expect($user->fresh()->enrolledDecks()->where('deck_id', $childDeck2->id)->exists())->toBeFalse();
});

test('can unenroll from individual deck', function () {
    $user = actingAsUser();
    $collection = Deck::factory()->create(['is_collection' => true]);
    $childDeck = Deck::factory()->create(['parent_deck_id' => $collection->id]);

    // Enroll in child deck only
    $user->enrolledDecks()->attach($childDeck->id, [
        'enrolled_at' => now(),
        'shortcode' => 'child004',
    ]);

    Livewire::test(CollectionDetail::class, ['collectionId' => $collection->id])
        ->call('unenrollFromChildDeck', $childDeck->id)
        ->assertDispatched('deck-unenrolled');

    expect($user->fresh()->enrolledDecks()->where('deck_id', $childDeck->id)->exists())->toBeFalse();
});

test('library browse shows view collection button', function () {
    $user = actingAsUser();
    $collection = Deck::factory()->create([
        'is_collection' => true,
        'name' => 'Wine Fundamentals',
    ]);

    Livewire::test(Library::class)
        ->call('switchTab', 'browse')
        ->assertSee('Wine Fundamentals')
        ->assertSee('View Collection')
        ->assertSee('Add Collection to Library');
});

test('individually enrolled child deck does not show in my collections', function () {
    $user = actingAsUser();
    $collection = Deck::factory()->create([
        'is_collection' => true,
        'name' => 'Full Collection',
    ]);

    $childDeck = Deck::factory()->create([
        'parent_deck_id' => $collection->id,
        'name' => 'Single Deck',
    ]);

    // Only enroll in child, not collection
    $user->enrolledDecks()->attach($childDeck->id, [
        'enrolled_at' => now(),
        'shortcode' => 'child005',
    ]);

    Livewire::test(Library::class)
        ->call('switchTab', 'enrolled')
        ->assertSee('Single Deck')
        ->assertSee('My Decks')
        ->assertDontSee('My Collections') // Collections section should not appear
        ->assertSee('From: Full Collection'); // But should see parent reference on the child deck
});

test('child deck enrolled through collection does not show parent name in my decks', function () {
    $user = actingAsUser();
    $collection = Deck::factory()->create([
        'is_collection' => true,
        'name' => 'Complete Set',
    ]);

    $childDeck = Deck::factory()->create([
        'parent_deck_id' => $collection->id,
        'name' => 'Part One',
    ]);

    // Enroll in entire collection
    $user->enrolledDecks()->attach($collection->id, [
        'enrolled_at' => now(),
        'shortcode' => 'collect3',
    ]);
    $user->enrolledDecks()->attach($childDeck->id, [
        'enrolled_at' => now(),
        'shortcode' => 'child006',
    ]);

    $component = Livewire::test(Library::class)
        ->call('switchTab', 'enrolled');

    // Should see it in collections, not standalone decks section
    $component->assertSee('Complete Set')
        ->assertSee('My Collections');
});

