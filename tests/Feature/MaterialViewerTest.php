<?php

declare(strict_types=1);

use Domain\Deck\Models\Deck;
use Domain\Material\Models\Material;
use Domain\User\Models\User;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

test('user sees materials on first study session', function () {
    $deck = Deck::factory()->create();
    $this->user->enrolledDecks()->attach($deck->id, [
        'enrolled_at' => now(),
        'shortcode' => 'TEST1234',
    ]);

    Material::factory()->for($deck)->create([
        'title' => 'Welcome',
        'content' => '<p>Welcome to this deck</p>',
    ]);

    Livewire::test('study-interface', [
        'type' => 'normal',
        'deck' => 'TEST1234',
    ])
        ->assertSet('phase', 'materials')
        ->assertSee('Welcome');
});

test('user can skip materials', function () {
    $deck = Deck::factory()->create();
    $this->user->enrolledDecks()->attach($deck->id, [
        'enrolled_at' => now(),
        'shortcode' => 'TEST1234',
    ]);

    Material::factory()->for($deck)->create();

    Livewire::test('study-interface', [
        'type' => 'normal',
        'deck' => 'TEST1234',
    ])
        ->assertSet('phase', 'materials')
        ->call('skipMaterials')
        ->assertSet('phase', 'intermediary');

    // Verify materials marked as viewed
    $hasViewed = DB::table('deck_user')
        ->where('user_id', $this->user->id)
        ->where('deck_id', $deck->id)
        ->value('has_viewed_materials');

    expect($hasViewed)->toBe(1);
});

test('user can navigate through materials', function () {
    $deck = Deck::factory()->create();
    $this->user->enrolledDecks()->attach($deck->id, [
        'enrolled_at' => now(),
        'shortcode' => 'TEST1234',
    ]);

    Material::factory()->for($deck)->count(3)->create();

    $component = Livewire::test('study-interface', [
        'type' => 'normal',
        'deck' => 'TEST1234',
    ])
        ->assertSet('current_material_index', 0)
        ->call('nextMaterial')
        ->assertSet('current_material_index', 1)
        ->call('nextMaterial')
        ->assertSet('current_material_index', 2)
        ->call('previousMaterial')
        ->assertSet('current_material_index', 1);
});

test('user proceeds to intermediary screen after completing materials', function () {
    $deck = Deck::factory()->create();
    $this->user->enrolledDecks()->attach($deck->id, [
        'enrolled_at' => now(),
        'shortcode' => 'TEST1234',
    ]);

    Material::factory()->for($deck)->create();

    Livewire::test('study-interface', [
        'type' => 'normal',
        'deck' => 'TEST1234',
    ])
        ->assertSet('phase', 'materials')
        ->call('completeMaterials')
        ->assertSet('phase', 'intermediary')
        ->assertSee('Ready to Begin?');
});

test('user proceeds to flashcards from intermediary screen', function () {
    $deck = Deck::factory()->create();
    $this->user->enrolledDecks()->attach($deck->id, [
        'enrolled_at' => now(),
        'shortcode' => 'TEST1234',
    ]);

    Material::factory()->for($deck)->create();

    Livewire::test('study-interface', [
        'type' => 'normal',
        'deck' => 'TEST1234',
    ])
        ->call('completeMaterials')
        ->assertSet('phase', 'intermediary')
        ->call('beginFlashcards')
        ->assertSet('phase', 'flashcards');
});

test('user skips materials phase if already viewed', function () {
    $deck = Deck::factory()->create();
    $this->user->enrolledDecks()->attach($deck->id, [
        'enrolled_at' => now(),
        'shortcode' => 'TEST1234',
        'has_viewed_materials' => true,
    ]);

    Material::factory()->for($deck)->create();

    Livewire::test('study-interface', [
        'type' => 'normal',
        'deck' => 'TEST1234',
    ])
        ->assertSet('phase', 'flashcards');
});

test('user can review materials from deck stats page', function () {
    $deck = Deck::factory()->create();
    $this->user->enrolledDecks()->attach($deck->id, [
        'enrolled_at' => now(),
        'shortcode' => 'TEST1234',
    ]);

    Material::factory()->for($deck)->create([
        'title' => 'Review This',
    ]);

    $this->get(route('deck.materials', ['shortcode' => 'TEST1234']))
        ->assertOk()
        ->assertSee('Review This');
});

test('deck stats page shows review materials link when deck has materials', function () {
    $deck = Deck::factory()->create();
    $this->user->enrolledDecks()->attach($deck->id, [
        'enrolled_at' => now(),
        'shortcode' => 'TEST1234',
    ]);

    Material::factory()->for($deck)->create();

    Livewire::test('deck-stats', ['shortcode' => 'TEST1234'])
        ->assertSeeHtml('Review Materials');
});

test('deck stats page hides review materials link when deck has no materials', function () {
    $deck = Deck::factory()->create();
    $this->user->enrolledDecks()->attach($deck->id, [
        'enrolled_at' => now(),
        'shortcode' => 'TEST1234',
    ]);

    Livewire::test('deck-stats', ['shortcode' => 'TEST1234'])
        ->assertDontSee('Review Materials');
});
