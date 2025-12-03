<?php

declare(strict_types=1);

use Domain\Deck\Data\DeckData;
use Domain\Deck\Models\Deck;
use Domain\Deck\Repositories\DeckRepository;

test('deck repository can get all decks', function () {
    Deck::factory()->create(['name' => 'Deck 1']);
    Deck::factory()->create(['name' => 'Deck 2']);
    Deck::factory()->create(['name' => 'Deck 3']);

    $repository = new DeckRepository();
    $result = $repository->getAll();

    expect($result)->toHaveCount(3)
        ->and($result->first())->toBeInstanceOf(DeckData::class);
});

test('deck repository returns empty collection when no decks exist', function () {
    $repository = new DeckRepository();
    $result = $repository->getAll();

    expect($result)->toBeEmpty();
});

test('deck repository can get active decks only', function () {
    Deck::factory()->create(['name' => 'Active 1', 'is_active' => true]);
    Deck::factory()->create(['name' => 'Active 2', 'is_active' => true]);
    Deck::factory()->create(['name' => 'Inactive', 'is_active' => false]);

    $repository = new DeckRepository();
    $result = $repository->getActive();

    expect($result)->toHaveCount(2);
    $names = $result->pluck('name')->toArray();
    expect($names)->toContain('Active 1', 'Active 2')
        ->and($names)->not->toContain('Inactive');
});

test('deck repository can find deck by id', function () {
    $deck = Deck::factory()->create([
        'name' => 'Test Deck',
        'description' => 'Test Description',
        'is_active' => true,
    ]);

    $repository = new DeckRepository();
    $result = $repository->findById($deck->id);

    expect($result)->toBeInstanceOf(DeckData::class)
        ->and($result->id)->toBe($deck->id)
        ->and($result->name)->toBe('Test Deck')
        ->and($result->description)->toBe('Test Description')
        ->and($result->is_active)->toBeTrue();
});

test('deck repository returns null when deck not found by id', function () {
    $repository = new DeckRepository();
    $result = $repository->findById(999999);

    expect($result)->toBeNull();
});

test('deck repository preserves all deck data fields', function () {
    $user = \Domain\User\Models\User::factory()->create();
    
    $deck = Deck::factory()->create([
        'name' => 'Full Deck',
        'description' => 'Complete description',
        'is_active' => true,
        'created_by' => $user->id,
    ]);

    $repository = new DeckRepository();
    $result = $repository->findById($deck->id);

    expect($result->name)->toBe('Full Deck')
        ->and($result->description)->toBe('Complete description')
        ->and($result->is_active)->toBeTrue()
        ->and($result->created_by)->toBe($user->id);
});

test('deck repository handles decks with null description', function () {
    $deck = Deck::factory()->create([
        'name' => 'No Description',
        'description' => null,
    ]);

    $repository = new DeckRepository();
    $result = $repository->findById($deck->id);

    expect($result->description)->toBeNull();
});

test('deck repository getActive returns empty when no active decks', function () {
    Deck::factory()->count(3)->create(['is_active' => false]);

    $repository = new DeckRepository();
    $result = $repository->getActive();

    expect($result)->toBeEmpty();
});

test('deck repository getAll includes both active and inactive decks', function () {
    Deck::factory()->create(['is_active' => true]);
    Deck::factory()->create(['is_active' => false]);

    $repository = new DeckRepository();
    $result = $repository->getAll();

    expect($result)->toHaveCount(2);
});

test('deck repository returns decks in consistent order', function () {
    $deck1 = Deck::factory()->create(['name' => 'Deck A', 'created_at' => now()->subDays(3)]);
    $deck2 = Deck::factory()->create(['name' => 'Deck B', 'created_at' => now()->subDays(2)]);
    $deck3 = Deck::factory()->create(['name' => 'Deck C', 'created_at' => now()->subDays(1)]);

    $repository = new DeckRepository();
    $result = $repository->getAll();

    expect($result)->toHaveCount(3);
    $names = $result->pluck('name')->toArray();
    expect($names)->toContain('Deck A', 'Deck B', 'Deck C');
});

test('deck repository handles very long deck names', function () {
    $longName = str_repeat('A', 255);
    $deck = Deck::factory()->create(['name' => $longName]);

    $repository = new DeckRepository();
    $result = $repository->findById($deck->id);

    expect($result->name)->toBe($longName);
});

test('deck repository handles very long descriptions', function () {
    $longDescription = str_repeat('Lorem ipsum dolor sit amet. ', 100);
    $deck = Deck::factory()->create(['description' => $longDescription]);

    $repository = new DeckRepository();
    $result = $repository->findById($deck->id);

    expect($result->description)->toBe($longDescription);
});

test('deck repository handles unicode characters in name', function () {
    $deck = Deck::factory()->create(['name' => 'Test Üñíçødé 测试']);

    $repository = new DeckRepository();
    $result = $repository->findById($deck->id);

    expect($result->name)->toBe('Test Üñíçødé 测试');
});

test('deck repository handles special characters in description', function () {
    $deck = Deck::factory()->create(['description' => 'Test & <special> "characters" \'here\'']);

    $repository = new DeckRepository();
    $result = $repository->findById($deck->id);

    expect($result->description)->toBe('Test & <special> "characters" \'here\'');
});

test('deck repository getActive filters correctly with mixed states', function () {
    Deck::factory()->create(['name' => 'Active 1', 'is_active' => true]);
    Deck::factory()->create(['name' => 'Inactive 1', 'is_active' => false]);
    Deck::factory()->create(['name' => 'Active 2', 'is_active' => true]);
    Deck::factory()->create(['name' => 'Inactive 2', 'is_active' => false]);
    Deck::factory()->create(['name' => 'Active 3', 'is_active' => true]);

    $repository = new DeckRepository();
    $result = $repository->getActive();

    expect($result)->toHaveCount(3);
    $names = $result->pluck('name')->toArray();
    expect($names)->toContain('Active 1', 'Active 2', 'Active 3')
        ->and($names)->not->toContain('Inactive 1', 'Inactive 2');
});

