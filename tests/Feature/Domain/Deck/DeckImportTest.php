<?php

declare(strict_types=1);

use Domain\Card\Enums\CardType;
use Domain\Deck\Enums\ImportFormat;
use Domain\Deck\Enums\ImportStatus;
use Domain\Deck\Jobs\ProcessDeckImportJob;
use Domain\Deck\Models\DeckImport;
use Domain\Deck\Services\CsvImportService;
use Domain\Deck\Services\ImportValidationService;
use Domain\Deck\Services\TxtImportService;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

test('can parse valid CSV file with multiple choice cards', function (): void {
    $csvContent = <<<CSV
question,answer,image_path,card_type,answer_choices,correct_answer_indices
"What color is red wine?","Red","","multiple_choice","[""Red"",""White"",""Rose""]","[0]"
"What grape makes Champagne?","Chardonnay, Pinot Noir, Pinot Meunier","","multiple_choice","[""Cabernet"",""Chardonnay"",""Pinot Noir"",""Pinot Meunier""]","[1,2,3]"
CSV;

    Storage::fake('local');
    $path = storage_path('app/test-import.csv');
    file_put_contents($path, $csvContent);

    $service = new CsvImportService();
    $cards = $service->parseCSV($path);

    expect($cards)->toHaveCount(2)
        ->and($cards[0]['question'])->toBe('What color is red wine?')
        ->and($cards[0]['card_type'])->toBe(CardType::MULTIPLE_CHOICE)
        ->and($cards[0]['answer_choices'])->toBeArray()
        ->and($cards[1]['question'])->toBe('What grape makes Champagne?')
        ->and($cards[1]['correct_answer_indices'])->toBe([1, 2, 3]);

    unlink($path);
});

test('can parse CSV with multiple correct answers', function (): void {
    $csvContent = <<<CSV
question,answer,image_path,card_type,answer_choices,correct_answer_indices
"Which are red grapes?","Merlot, Syrah","","multiple_choice","[""Chardonnay"",""Merlot"",""Riesling"",""Syrah""]","[1,3]"
CSV;

    $path = storage_path('app/test-import-multi.csv');
    file_put_contents($path, $csvContent);

    $service = new CsvImportService();
    $cards = $service->parseCSV($path);

    expect($cards)->toHaveCount(1)
        ->and($cards[0]['correct_answer_indices'])->toBe([1, 3]);

    unlink($path);
});

test('CSV parser supports legacy single integer format', function (): void {
    $csvContent = <<<CSV
question,answer,image_path,card_type,answer_choices,correct_answer_indices
"What color is red wine?","Red","","multiple_choice","[""Red"",""White"",""Rose""]","0"
CSV;

    $path = storage_path('app/test-import-legacy.csv');
    file_put_contents($path, $csvContent);

    $service = new CsvImportService();
    $cards = $service->parseCSV($path);

    expect($cards)->toHaveCount(1)
        ->and($cards[0]['correct_answer_indices'])->toBe([0]);

    unlink($path);
});

test('CSV parser skips empty rows', function (): void {
    $csvContent = <<<CSV
question,answer,image_path,card_type,answer_choices,correct_answer_indices
"Valid question","Valid answer","","multiple_choice","[""A"",""B"",""C""]","[0]"
,,,,,
"Another question","Another answer","","multiple_choice","[""X"",""Y"",""Z""]","[1]"
CSV;

    $path = storage_path('app/test-import-empty.csv');
    file_put_contents($path, $csvContent);

    $service = new CsvImportService();
    $cards = $service->parseCSV($path);

    expect($cards)->toHaveCount(2);

    unlink($path);
});

test('CSV validator validates multiple choice cards', function (): void {
    $service = new CsvImportService();

    $validMCCard = [
        'question' => 'Test question',
        'answer' => 'Test answer',
        'image_path' => null,
        'card_type' => CardType::MULTIPLE_CHOICE,
        'answer_choices' => ['A', 'B', 'C'],
        'correct_answer_indices' => [1],
    ];

    expect($service->validate($validMCCard))->toBeTrue();
});

test('CSV validator validates multiple choice cards with multiple correct answers', function (): void {
    $service = new CsvImportService();

    $validMCCard = [
        'question' => 'Test question',
        'answer' => 'Test answer',
        'image_path' => null,
        'card_type' => CardType::MULTIPLE_CHOICE,
        'answer_choices' => ['A', 'B', 'C', 'D'],
        'correct_answer_indices' => [1, 3],
    ];

    expect($service->validate($validMCCard))->toBeTrue();
});

test('CSV validator requires question and answer', function (): void {
    $service = new CsvImportService();

    $invalidCard = [
        'question' => '',
        'answer' => 'Test answer',
        'image_path' => null,
        'card_type' => CardType::MULTIPLE_CHOICE,
        'answer_choices' => ['A', 'B', 'C'],
        'correct_answer_indices' => [0],
    ];

    expect($service->validate($invalidCard))->toBeFalse();
});

test('CSV validator rejects cards with insufficient choices', function (): void {
    $service = new CsvImportService();

    $invalidMCCard = [
        'question' => 'Test question',
        'answer' => 'Test answer',
        'image_path' => null,
        'card_type' => CardType::MULTIPLE_CHOICE,
        'answer_choices' => ['A'],
        'correct_answer_indices' => [0],
    ];

    expect($service->validate($invalidMCCard))->toBeFalse();
});

test('CSV validator rejects cards with empty correct answer indices', function (): void {
    $service = new CsvImportService();

    $invalidMCCard = [
        'question' => 'Test question',
        'answer' => 'Test answer',
        'image_path' => null,
        'card_type' => CardType::MULTIPLE_CHOICE,
        'answer_choices' => ['A', 'B', 'C'],
        'correct_answer_indices' => [],
    ];

    expect($service->validate($invalidMCCard))->toBeFalse();
});

test('CSV validator rejects cards with invalid answer indices', function (): void {
    $service = new CsvImportService();

    $invalidMCCard = [
        'question' => 'Test question',
        'answer' => 'Test answer',
        'image_path' => null,
        'card_type' => CardType::MULTIPLE_CHOICE,
        'answer_choices' => ['A', 'B', 'C'],
        'correct_answer_indices' => [5],
    ];

    expect($service->validate($invalidMCCard))->toBeFalse();
});

test('CSV validator rejects cards without answer choices', function (): void {
    $service = new CsvImportService();

    $invalidCard = [
        'question' => 'Test question',
        'answer' => 'Test answer',
        'image_path' => null,
        'card_type' => CardType::MULTIPLE_CHOICE,
        'answer_choices' => null,
        'correct_answer_indices' => null,
    ];

    expect($service->validate($invalidCard))->toBeFalse();
});

test('import action creates pending import and dispatches job', function (): void {
    Queue::fake();

    $admin = \Domain\Admin\Models\Admin::factory()->create();

    $csvContent = <<<CSV
question,answer,image_path,card_type,answer_choices,correct_answer_indices
"What is wine?","Fermented grape juice","","multiple_choice","[""Fermented grape juice"",""Fruit juice"",""Soda"",""Water""]","[0]"
"What grape is Bordeaux famous for?","Cabernet Sauvignon","","multiple_choice","[""Pinot Noir"",""Cabernet Sauvignon"",""Merlot"",""Syrah""]","[1]"
CSV;

    $path = storage_path('app/test-deck-import.csv');
    file_put_contents($path, $csvContent);

    $action = app(\Domain\Deck\Actions\ImportDeckAction::class);

    $result = $action->execute(
        userId: $admin->id,
        filePath: $path,
        originalFilename: 'test-deck-import.csv',
        format: ImportFormat::CSV,
        deckName: 'Test Wine Deck',
        description: 'A test deck'
    );

    expect($result->status)->toBe(ImportStatus::PENDING)
        ->and($result->total_rows)->toBe(2);

    Queue::assertPushed(ProcessDeckImportJob::class);

    unlink($path);
});

test('import validation service validates CSV file structure', function (): void {
    $csvContent = <<<CSV
question,answer,image_path,card_type,answer_choices,correct_answer_indices
"What is wine?","Fermented grape juice","","multiple_choice","[""A"",""B""]","[0]"
CSV;

    $path = storage_path('app/test-validation.csv');
    file_put_contents($path, $csvContent);

    $service = new ImportValidationService();
    $result = $service->validateFile($path, ImportFormat::CSV);

    expect($result['valid'])->toBeTrue()
        ->and($result['row_count'])->toBe(1);

    unlink($path);
});

test('import validation service detects invalid rows', function (): void {
    // Create a CSV with one valid row and one row with missing answer
    $csvContent = "question,answer,image_path,card_type,answer_choices,correct_answer_indices\n";
    $csvContent .= '"Valid question","Valid answer","","multiple_choice","[""A"",""B""]","[0]"' . "\n";
    $csvContent .= '"Invalid question","","","multiple_choice","[""A"",""B""]",""' . "\n"; // Missing answer and indices

    $path = storage_path('app/test-validation-invalid.csv');
    file_put_contents($path, $csvContent);

    $service = new ImportValidationService();
    $result = $service->validateFile($path, ImportFormat::CSV);

    // File should have errors due to invalid row
    expect($result['row_count'])->toBe(2)
        ->and($result['invalid_rows'])->toBeGreaterThan(0);

    unlink($path);
});

test('can parse valid TXT file with tab-delimited columns', function (): void {
    $txtContent = "question\tanswer\timage_path\tcard_type\tanswer_choices\tcorrect_answer_indices\n";
    $txtContent .= "What color is red wine?\tRed\t\tmultiple_choice\t[\"Red\",\"White\",\"Rose\"]\t[0]\n";
    $txtContent .= "What grape makes Champagne?\tChardonnay, Pinot Noir\t\tmultiple_choice\t[\"Cabernet\",\"Chardonnay\"]\t[1]\n";

    $path = storage_path('app/test-import.txt');
    file_put_contents($path, $txtContent);

    $service = new TxtImportService();
    $cards = $service->parseTXT($path);

    expect($cards)->toHaveCount(2)
        ->and($cards[0]['question'])->toBe('What color is red wine?')
        ->and($cards[0]['card_type'])->toBe(CardType::MULTIPLE_CHOICE)
        ->and($cards[0]['answer_choices'])->toBeArray()
        ->and($cards[1]['question'])->toBe('What grape makes Champagne?');

    unlink($path);
});

test('TXT validator validates multiple choice cards', function (): void {
    $service = new TxtImportService();

    $validMCCard = [
        'question' => 'Test question',
        'answer' => 'Test answer',
        'image_path' => null,
        'card_type' => CardType::MULTIPLE_CHOICE,
        'answer_choices' => ['A', 'B', 'C'],
        'correct_answer_indices' => [1],
    ];

    expect($service->validate($validMCCard))->toBeTrue();
});

test('import job processes cards and creates deck', function (): void {
    $admin = \Domain\Admin\Models\Admin::factory()->create();

    $csvContent = <<<CSV
question,answer,image_path,card_type,answer_choices,correct_answer_indices
"What is wine?","Fermented grape juice","","multiple_choice","[""Fermented grape juice"",""Fruit juice"",""Soda"",""Water""]","[0]"
"What grape is Bordeaux famous for?","Cabernet Sauvignon","","multiple_choice","[""Pinot Noir"",""Cabernet Sauvignon"",""Merlot"",""Syrah""]","[1]"
CSV;

    $storedPath = 'imports/test-job-import.csv';
    $fullPath = storage_path('app/' . $storedPath);
    
    // Ensure directory exists
    if (! is_dir(dirname($fullPath))) {
        mkdir(dirname($fullPath), 0755, true);
    }
    
    file_put_contents($fullPath, $csvContent);

    // Create import record
    $import = DeckImport::create([
        'user_id' => $admin->id,
        'filename' => 'test-job-import.csv',
        'original_filename' => 'Test Wine Deck',
        'file_path' => $storedPath,
        'format' => ImportFormat::CSV->value,
        'status' => ImportStatus::PENDING->value,
        'total_rows' => 2,
    ]);

    // Run the job synchronously
    $job = new ProcessDeckImportJob($import->id);
    $job->handle(
        app(CsvImportService::class),
        app(TxtImportService::class),
        app(\Domain\Card\Actions\CreateCardAction::class),
        app(\Domain\Card\Actions\UpdateCardAction::class)
    );

    $import->refresh();

    expect($import->status)->toBe(ImportStatus::COMPLETED)
        ->and($import->imported_cards_count)->toBe(2)
        ->and($import->deck_id)->not->toBeNull();

    $this->assertDatabaseHas('decks', [
        'id' => $import->deck_id,
        'name' => 'Test Wine Deck',
    ]);

    $this->assertDatabaseHas('cards', [
        'deck_id' => $import->deck_id,
        'question' => 'What is wine?',
    ]);
});

test('import job handles errors gracefully', function (): void {
    $admin = \Domain\Admin\Models\Admin::factory()->create();

    // Create import record with non-existent file
    $import = DeckImport::create([
        'user_id' => $admin->id,
        'filename' => 'nonexistent.csv',
        'original_filename' => 'Test Deck',
        'file_path' => 'imports/nonexistent.csv',
        'format' => ImportFormat::CSV->value,
        'status' => ImportStatus::PENDING->value,
    ]);

    // Run the job synchronously
    $job = new ProcessDeckImportJob($import->id);
    $job->handle(
        app(CsvImportService::class),
        app(TxtImportService::class),
        app(\Domain\Card\Actions\CreateCardAction::class),
        app(\Domain\Card\Actions\UpdateCardAction::class)
    );

    $import->refresh();

    expect($import->status)->toBe(ImportStatus::FAILED)
        ->and($import->error_message)->not->toBeNull();
});
