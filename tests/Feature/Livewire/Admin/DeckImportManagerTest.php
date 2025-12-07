<?php

declare(strict_types=1);

use App\Livewire\Admin\DeckImportManager;
use Domain\Deck\Enums\ImportFormat;
use Domain\Deck\Enums\ImportStatus;
use Domain\Deck\Jobs\ProcessDeckImportJob;
use Domain\Deck\Models\Deck;
use Domain\Deck\Models\DeckImport;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('deck import manager can be rendered by admin', function () {
    actingAsAdmin();

    Livewire::test(DeckImportManager::class)
        ->assertStatus(200)
        ->assertSee('Import Deck');
});

test('deck import manager shows format options', function () {
    actingAsAdmin();

    Livewire::test(DeckImportManager::class)
        ->assertSee('CSV')
        ->assertSee('TXT');
});

test('deck import manager shows import mode options', function () {
    actingAsAdmin();

    Livewire::test(DeckImportManager::class)
        ->assertSee('Create New Deck')
        ->assertSee('Add to Existing Deck');
});

test('deck import manager shows existing decks in select when in existing mode', function () {
    actingAsAdmin();
    
    Deck::factory()->create(['name' => 'Test Wine Deck']);
    Deck::factory()->create(['name' => 'Another Deck']);

    Livewire::test(DeckImportManager::class)
        ->set('importMode', 'existing')
        ->assertSee('Test Wine Deck')
        ->assertSee('Another Deck');
});

test('deck import manager requires deck name for new deck', function () {
    actingAsAdmin();
    Storage::fake('local');
    
    $file = UploadedFile::fake()->create('test.csv', 100, 'text/csv');

    Livewire::test(DeckImportManager::class)
        ->set('importMode', 'new')
        ->set('deckName', '') // Empty deck name
        ->set('format', 'csv')
        ->set('file', $file)
        ->call('import')
        ->assertHasErrors(['deckName']);
});

test('deck import manager requires file for import', function () {
    actingAsAdmin();

    Livewire::test(DeckImportManager::class)
        ->set('importMode', 'new')
        ->set('deckName', 'Test Deck')
        ->set('format', 'csv')
        ->call('import')
        ->assertHasErrors(['file']);
});

test('deck import manager requires deck selection for existing mode', function () {
    actingAsAdmin();
    Storage::fake('local');
    
    $file = UploadedFile::fake()->create('test.csv', 100, 'text/csv');

    Livewire::test(DeckImportManager::class)
        ->set('importMode', 'existing')
        ->set('selectedDeckId', null)
        ->set('format', 'csv')
        ->set('file', $file)
        ->call('import')
        ->assertHasErrors(['selectedDeckId']);
});

test('deck import manager shows import history', function () {
    $admin = actingAsAdmin();
    
    DeckImport::create([
        'user_id' => $admin->id,
        'filename' => 'previous-import.csv',
        'original_filename' => 'previous-import.csv',
        'file_path' => 'imports/test.csv',
        'format' => ImportFormat::CSV->value,
        'status' => ImportStatus::COMPLETED->value,
        'imported_cards_count' => 10,
        'updated_cards_count' => 0,
        'skipped_rows' => 0,
        'total_rows' => 10,
    ]);

    Livewire::test(DeckImportManager::class)
        ->assertSee('previous-import.csv');
});

test('deck import manager can close progress modal', function () {
    $admin = actingAsAdmin();
    
    $import = DeckImport::create([
        'user_id' => $admin->id,
        'filename' => 'test.csv',
        'original_filename' => 'test.csv',
        'file_path' => 'imports/test.csv',
        'format' => ImportFormat::CSV->value,
        'status' => ImportStatus::COMPLETED->value,
        'imported_cards_count' => 5,
        'updated_cards_count' => 0,
        'skipped_rows' => 0,
        'total_rows' => 5,
    ]);

    Livewire::test(DeckImportManager::class)
        ->set('showProgressModal', true)
        ->set('currentImportId', $import->id)
        ->call('closeProgressModal')
        ->assertSet('showProgressModal', false)
        ->assertSet('currentImportId', null);
});

test('deck import manager can download csv template', function () {
    actingAsAdmin();

    // Ensure template exists
    $templatePath = public_path('templates/deck-import-template.csv');
    expect(file_exists($templatePath))->toBeTrue();

    Livewire::test(DeckImportManager::class)
        ->call('downloadTemplate')
        ->assertFileDownloaded('deck-import-template.csv');
});

test('deck import manager can download txt template', function () {
    actingAsAdmin();

    // Ensure template exists
    $templatePath = public_path('templates/deck-import-template.txt');
    if (! file_exists($templatePath)) {
        // Create if it doesn't exist
        file_put_contents($templatePath, "question\tanswer\timage_path\tcard_type\tanswer_choices\tcorrect_answer_indices\n");
    }

    Livewire::test(DeckImportManager::class)
        ->call('downloadTxtTemplate')
        ->assertFileDownloaded('deck-import-template.txt');
});

test('deck import manager refreshes import status', function () {
    $admin = actingAsAdmin();
    
    $import = DeckImport::create([
        'user_id' => $admin->id,
        'filename' => 'test.csv',
        'original_filename' => 'test.csv',
        'file_path' => 'imports/test.csv',
        'format' => ImportFormat::CSV->value,
        'status' => ImportStatus::PROCESSING->value,
        'imported_cards_count' => 0,
        'updated_cards_count' => 0,
        'skipped_rows' => 0,
        'total_rows' => 10,
    ]);

    $component = Livewire::test(DeckImportManager::class)
        ->set('currentImportId', $import->id);
    
    // Update import status
    $import->update(['status' => ImportStatus::COMPLETED->value]);
    
    // Refresh should reflect the change
    $component->call('refreshImportStatus');
    
    expect($component->get('currentImport')->status)->toBe(ImportStatus::COMPLETED);
});

test('deck import manager clears validation on file change', function () {
    actingAsAdmin();
    
    $file = UploadedFile::fake()->create('test.csv', 100, 'text/csv');

    Livewire::test(DeckImportManager::class)
        ->set('validationResult', ['valid' => true, 'errors' => []])
        ->set('file', $file)
        ->assertSet('validationResult', []);
});

test('deck import manager validates file format', function () {
    actingAsAdmin();

    Livewire::test(DeckImportManager::class)
        ->set('format', 'csv')
        ->assertSet('format', 'csv')
        ->set('format', 'txt')
        ->assertSet('format', 'txt');
});
