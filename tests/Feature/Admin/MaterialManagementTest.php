<?php

declare(strict_types=1);

use Domain\Admin\Models\Admin;
use Domain\Deck\Models\Deck;
use Domain\Material\Models\Material;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    $this->admin = Admin::factory()->create();
    $this->actingAs($this->admin, 'admin');
});

test('admin can view materials management page', function () {
    $deck = Deck::factory()->create();

    $this->get(route('admin.decks.materials', $deck->id))
        ->assertOk()
        ->assertSeeLivewire('admin.deck-materials');
});

test('admin can create a material without image', function () {
    $deck = Deck::factory()->create();

    Livewire::test('admin.deck-materials', ['deck_id' => $deck->id])
        ->set('title', 'Introduction to Wine')
        ->set('content', '<p>This is the introduction content.</p>')
        ->set('image_position', 'top')
        ->call('createMaterial')
        ->assertDispatched('material-saved');

    expect(Material::count())->toBe(1);
    expect(Material::first())
        ->title->toBe('Introduction to Wine')
        ->content->toBe('<p>This is the introduction content.</p>')
        ->deck_id->toBe($deck->id);
});

test('admin can create a material with image', function () {
    $deck = Deck::factory()->create();
    $file = UploadedFile::fake()->image('material.jpg');

    Livewire::test('admin.deck-materials', ['deck_id' => $deck->id])
        ->set('title', 'Wine Regions')
        ->set('content', '<p>Learn about wine regions.</p>')
        ->set('image', $file)
        ->set('image_position', 'left')
        ->call('createMaterial')
        ->assertDispatched('material-saved');

    expect(Material::count())->toBe(1);
    $material = Material::first();
    expect($material->image_path)->not->toBeNull();
    Storage::disk('public')->assertExists($material->image_path);
});

test('admin can update a material', function () {
    $deck = Deck::factory()->create();
    $material = Material::factory()->for($deck)->create([
        'title' => 'Old Title',
        'content' => '<p>Old content</p>',
    ]);

    Livewire::test('admin.deck-materials', ['deck_id' => $deck->id])
        ->call('openEditModal', $material->id)
        ->set('title', 'New Title')
        ->set('content', '<p>New content</p>')
        ->call('updateMaterial')
        ->assertDispatched('material-saved');

    expect($material->fresh())
        ->title->toBe('New Title')
        ->content->toBe('<p>New content</p>');
});

test('admin can delete a material', function () {
    $deck = Deck::factory()->create();
    $material = Material::factory()->for($deck)->create();

    expect(Material::count())->toBe(1);

    Livewire::test('admin.deck-materials', ['deck_id' => $deck->id])
        ->call('deleteMaterial', $material->id)
        ->assertDispatched('material-saved');

    expect(Material::count())->toBe(0);
});

test('admin can reorder materials', function () {
    $deck = Deck::factory()->create();
    $material1 = Material::factory()->for($deck)->atPosition(0)->create();
    $material2 = Material::factory()->for($deck)->atPosition(1)->create();
    $material3 = Material::factory()->for($deck)->atPosition(2)->create();

    Livewire::test('admin.deck-materials', ['deck_id' => $deck->id])
        ->call('updateSortOrder', [$material3->id, $material1->id, $material2->id]);

    expect($material3->fresh()->sort_order)->toBe(0);
    expect($material1->fresh()->sort_order)->toBe(1);
    expect($material2->fresh()->sort_order)->toBe(2);
});

test('material content is required', function () {
    $deck = Deck::factory()->create();

    Livewire::test('admin.deck-materials', ['deck_id' => $deck->id])
        ->set('content', '')
        ->call('createMaterial')
        ->assertHasErrors(['content']);
});
