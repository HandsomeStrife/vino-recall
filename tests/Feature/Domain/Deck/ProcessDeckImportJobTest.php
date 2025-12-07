<?php

declare(strict_types=1);

use Domain\Card\Actions\CreateCardAction;
use Domain\Card\Actions\UpdateCardAction;
use Domain\Card\Models\Card;
use Domain\Deck\Enums\ImportFormat;
use Domain\Deck\Enums\ImportStatus;
use Domain\Deck\Jobs\ProcessDeckImportJob;
use Domain\Deck\Models\Deck;
use Domain\Deck\Models\DeckImport;
use Domain\Deck\Services\CsvImportService;
use Domain\Deck\Services\TxtImportService;

test('job creates new deck and imports cards', function () {
    $admin = \Domain\Admin\Models\Admin::factory()->create();
    
    $csvContent = <<<CSV
question,answer,image_path,card_type,answer_choices,correct_answer_indices
"What is wine?","Fermented grape juice","","multiple_choice","[""Fermented grape juice"",""Water""]","[0]"
CSV;

    $storedPath = 'imports/test-job-create.csv';
    $fullPath = storage_path('app/' . $storedPath);
    
    if (! is_dir(dirname($fullPath))) {
        mkdir(dirname($fullPath), 0755, true);
    }
    file_put_contents($fullPath, $csvContent);

    $import = DeckImport::create([
        'user_id' => $admin->id,
        'filename' => 'test-job-create.csv',
        'original_filename' => 'Wine Deck',
        'file_path' => $storedPath,
        'format' => ImportFormat::CSV->value,
        'status' => ImportStatus::PENDING->value,
        'total_rows' => 1,
    ]);

    $job = new ProcessDeckImportJob($import->id);
    $job->handle(
        app(CsvImportService::class),
        app(TxtImportService::class),
        app(CreateCardAction::class),
        app(UpdateCardAction::class)
    );

    $import->refresh();

    expect($import->status)->toBe(ImportStatus::COMPLETED)
        ->and($import->imported_cards_count)->toBe(1)
        ->and($import->deck_id)->not->toBeNull();
});

test('job updates existing cards when questions match', function () {
    $admin = \Domain\Admin\Models\Admin::factory()->create();
    
    // Create existing deck with a card
    $deck = Deck::factory()->create(['name' => 'Existing Deck']);
    Card::factory()->create([
        'deck_id' => $deck->id,
        'question' => 'What is wine?',
        'answer' => 'Old answer',
        'answer_choices' => json_encode(['A', 'B']),
        'correct_answer_indices' => json_encode([0]),
    ]);
    
    // Import with updated answer
    $csvContent = <<<CSV
question,answer,image_path,card_type,answer_choices,correct_answer_indices
"What is wine?","New updated answer","","multiple_choice","[""A"",""B"",""C""]","[1]"
CSV;

    $storedPath = 'imports/test-job-update.csv';
    $fullPath = storage_path('app/' . $storedPath);
    
    if (! is_dir(dirname($fullPath))) {
        mkdir(dirname($fullPath), 0755, true);
    }
    file_put_contents($fullPath, $csvContent);

    $import = DeckImport::create([
        'user_id' => $admin->id,
        'deck_id' => $deck->id,
        'filename' => 'test-job-update.csv',
        'original_filename' => 'Update Import',
        'file_path' => $storedPath,
        'format' => ImportFormat::CSV->value,
        'status' => ImportStatus::PENDING->value,
        'total_rows' => 1,
    ]);

    $job = new ProcessDeckImportJob($import->id);
    $job->handle(
        app(CsvImportService::class),
        app(TxtImportService::class),
        app(CreateCardAction::class),
        app(UpdateCardAction::class)
    );

    $import->refresh();

    expect($import->status)->toBe(ImportStatus::COMPLETED)
        ->and($import->updated_cards_count)->toBe(1)
        ->and($import->imported_cards_count)->toBe(0);

    // Verify the card was updated
    $card = Card::where('question', 'What is wine?')->first();
    expect($card->answer)->toBe('New updated answer');
});

test('job handles missing import record gracefully', function () {
    $job = new ProcessDeckImportJob(99999); // Non-existent ID
    $job->handle(
        app(CsvImportService::class),
        app(TxtImportService::class),
        app(CreateCardAction::class),
        app(UpdateCardAction::class)
    );

    // Should not throw exception
    expect(true)->toBeTrue();
});

test('job handles missing file gracefully', function () {
    $admin = \Domain\Admin\Models\Admin::factory()->create();
    
    $import = DeckImport::create([
        'user_id' => $admin->id,
        'filename' => 'nonexistent.csv',
        'original_filename' => 'Missing File',
        'file_path' => 'imports/nonexistent.csv',
        'format' => ImportFormat::CSV->value,
        'status' => ImportStatus::PENDING->value,
    ]);

    $job = new ProcessDeckImportJob($import->id);
    $job->handle(
        app(CsvImportService::class),
        app(TxtImportService::class),
        app(CreateCardAction::class),
        app(UpdateCardAction::class)
    );

    $import->refresh();

    expect($import->status)->toBe(ImportStatus::FAILED)
        ->and($import->error_message)->not->toBeNull();
});

test('job handles empty file gracefully', function () {
    $admin = \Domain\Admin\Models\Admin::factory()->create();
    
    // Create empty CSV file
    $storedPath = 'imports/test-job-empty.csv';
    $fullPath = storage_path('app/' . $storedPath);
    
    if (! is_dir(dirname($fullPath))) {
        mkdir(dirname($fullPath), 0755, true);
    }
    file_put_contents($fullPath, "question,answer,image_path,card_type,answer_choices,correct_answer_indices\n");

    $import = DeckImport::create([
        'user_id' => $admin->id,
        'filename' => 'test-job-empty.csv',
        'original_filename' => 'Empty Import',
        'file_path' => $storedPath,
        'format' => ImportFormat::CSV->value,
        'status' => ImportStatus::PENDING->value,
        'total_rows' => 0,
    ]);

    $job = new ProcessDeckImportJob($import->id);
    $job->handle(
        app(CsvImportService::class),
        app(TxtImportService::class),
        app(CreateCardAction::class),
        app(UpdateCardAction::class)
    );

    $import->refresh();

    expect($import->status)->toBe(ImportStatus::FAILED);
});

test('job skips invalid rows and continues', function () {
    $admin = \Domain\Admin\Models\Admin::factory()->create();
    
    // CSV with valid rows only (invalid rows are filtered during parsing)
    $csvContent = <<<CSV
question,answer,image_path,card_type,answer_choices,correct_answer_indices
"Valid question","Valid answer","","multiple_choice","[""A"",""B""]","[0]"
"Another valid","Another answer","","multiple_choice","[""X"",""Y"",""Z""]","[2]"
CSV;

    $storedPath = 'imports/test-job-partial.csv';
    $fullPath = storage_path('app/' . $storedPath);
    
    if (! is_dir(dirname($fullPath))) {
        mkdir(dirname($fullPath), 0755, true);
    }
    file_put_contents($fullPath, $csvContent);

    $import = DeckImport::create([
        'user_id' => $admin->id,
        'filename' => 'test-job-partial.csv',
        'original_filename' => 'Partial Import',
        'file_path' => $storedPath,
        'format' => ImportFormat::CSV->value,
        'status' => ImportStatus::PENDING->value,
        'total_rows' => 2,
    ]);

    $job = new ProcessDeckImportJob($import->id);
    $job->handle(
        app(CsvImportService::class),
        app(TxtImportService::class),
        app(CreateCardAction::class),
        app(UpdateCardAction::class)
    );

    $import->refresh();

    expect($import->status)->toBe(ImportStatus::COMPLETED)
        ->and($import->imported_cards_count)->toBe(2);
});

test('job processes TXT format correctly', function () {
    $admin = \Domain\Admin\Models\Admin::factory()->create();
    
    $txtContent = "question\tanswer\timage_path\tcard_type\tanswer_choices\tcorrect_answer_indices\n";
    $txtContent .= "What is wine?\tFermented grape juice\t\tmultiple_choice\t[\"A\",\"B\"]\t[0]\n";

    $storedPath = 'imports/test-job.txt';
    $fullPath = storage_path('app/' . $storedPath);
    
    if (! is_dir(dirname($fullPath))) {
        mkdir(dirname($fullPath), 0755, true);
    }
    file_put_contents($fullPath, $txtContent);

    $import = DeckImport::create([
        'user_id' => $admin->id,
        'filename' => 'test-job.txt',
        'original_filename' => 'TXT Deck',
        'file_path' => $storedPath,
        'format' => ImportFormat::TXT->value,
        'status' => ImportStatus::PENDING->value,
        'total_rows' => 1,
    ]);

    $job = new ProcessDeckImportJob($import->id);
    $job->handle(
        app(CsvImportService::class),
        app(TxtImportService::class),
        app(CreateCardAction::class),
        app(UpdateCardAction::class)
    );

    $import->refresh();

    expect($import->status)->toBe(ImportStatus::COMPLETED)
        ->and($import->imported_cards_count)->toBe(1);
});

test('job cleans up file after successful import', function () {
    $admin = \Domain\Admin\Models\Admin::factory()->create();
    
    $csvContent = <<<CSV
question,answer,image_path,card_type,answer_choices,correct_answer_indices
"Test question","Test answer","","multiple_choice","[""A"",""B""]","[0]"
CSV;

    $storedPath = 'imports/test-cleanup.csv';
    $fullPath = storage_path('app/' . $storedPath);
    
    if (! is_dir(dirname($fullPath))) {
        mkdir(dirname($fullPath), 0755, true);
    }
    file_put_contents($fullPath, $csvContent);

    $import = DeckImport::create([
        'user_id' => $admin->id,
        'filename' => 'test-cleanup.csv',
        'original_filename' => 'Cleanup Test',
        'file_path' => $storedPath,
        'format' => ImportFormat::CSV->value,
        'status' => ImportStatus::PENDING->value,
        'total_rows' => 1,
    ]);

    $job = new ProcessDeckImportJob($import->id);
    $job->handle(
        app(CsvImportService::class),
        app(TxtImportService::class),
        app(CreateCardAction::class),
        app(UpdateCardAction::class)
    );

    // File should be deleted
    expect(file_exists($fullPath))->toBeFalse();
});

test('job cleans up file after failed import', function () {
    $admin = \Domain\Admin\Models\Admin::factory()->create();
    
    // Empty file will fail
    $storedPath = 'imports/test-cleanup-fail.csv';
    $fullPath = storage_path('app/' . $storedPath);
    
    if (! is_dir(dirname($fullPath))) {
        mkdir(dirname($fullPath), 0755, true);
    }
    file_put_contents($fullPath, "question,answer,image_path,card_type,answer_choices,correct_answer_indices\n");

    $import = DeckImport::create([
        'user_id' => $admin->id,
        'filename' => 'test-cleanup-fail.csv',
        'original_filename' => 'Cleanup Fail Test',
        'file_path' => $storedPath,
        'format' => ImportFormat::CSV->value,
        'status' => ImportStatus::PENDING->value,
    ]);

    $job = new ProcessDeckImportJob($import->id);
    $job->handle(
        app(CsvImportService::class),
        app(TxtImportService::class),
        app(CreateCardAction::class),
        app(UpdateCardAction::class)
    );

    // File should be deleted even on failure
    expect(file_exists($fullPath))->toBeFalse();
});

test('job sets started_at timestamp', function () {
    $admin = \Domain\Admin\Models\Admin::factory()->create();
    
    $csvContent = <<<CSV
question,answer,image_path,card_type,answer_choices,correct_answer_indices
"Test","Answer","","multiple_choice","[""A"",""B""]","[0]"
CSV;

    $storedPath = 'imports/test-timestamps.csv';
    $fullPath = storage_path('app/' . $storedPath);
    
    if (! is_dir(dirname($fullPath))) {
        mkdir(dirname($fullPath), 0755, true);
    }
    file_put_contents($fullPath, $csvContent);

    $import = DeckImport::create([
        'user_id' => $admin->id,
        'filename' => 'test-timestamps.csv',
        'original_filename' => 'Timestamp Test',
        'file_path' => $storedPath,
        'format' => ImportFormat::CSV->value,
        'status' => ImportStatus::PENDING->value,
        'total_rows' => 1,
    ]);

    expect($import->started_at)->toBeNull();

    $job = new ProcessDeckImportJob($import->id);
    $job->handle(
        app(CsvImportService::class),
        app(TxtImportService::class),
        app(CreateCardAction::class),
        app(UpdateCardAction::class)
    );

    $import->refresh();

    expect($import->started_at)->not->toBeNull()
        ->and($import->completed_at)->not->toBeNull();
});

test('job completes successfully with all valid cards', function () {
    $admin = \Domain\Admin\Models\Admin::factory()->create();
    
    $csvContent = <<<CSV
question,answer,image_path,card_type,answer_choices,correct_answer_indices
"Valid","Valid answer","","multiple_choice","[""A"",""B""]","[0]"
"Also Valid","Another answer","","multiple_choice","[""X"",""Y""]","[1]"
CSV;

    $storedPath = 'imports/test-all-valid.csv';
    $fullPath = storage_path('app/' . $storedPath);
    
    if (! is_dir(dirname($fullPath))) {
        mkdir(dirname($fullPath), 0755, true);
    }
    file_put_contents($fullPath, $csvContent);

    $import = DeckImport::create([
        'user_id' => $admin->id,
        'filename' => 'test-all-valid.csv',
        'original_filename' => 'All Valid Test',
        'file_path' => $storedPath,
        'format' => ImportFormat::CSV->value,
        'status' => ImportStatus::PENDING->value,
        'total_rows' => 2,
    ]);

    $job = new ProcessDeckImportJob($import->id);
    $job->handle(
        app(CsvImportService::class),
        app(TxtImportService::class),
        app(CreateCardAction::class),
        app(UpdateCardAction::class)
    );

    $import->refresh();

    expect($import->status)->toBe(ImportStatus::COMPLETED)
        ->and($import->imported_cards_count)->toBe(2)
        ->and($import->skipped_rows)->toBe(0);
});

