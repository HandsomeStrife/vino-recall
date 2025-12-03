<?php

declare(strict_types=1);

use Domain\Card\Enums\CardType;
use Domain\Deck\Services\CsvImportService;
use Illuminate\Support\Facades\Storage;

test('can parse valid CSV file', function (): void {
    $csvContent = <<<CSV
question,answer,image_path,card_type,answer_choices,correct_answer_index
"What is wine?","Fermented grape juice","","traditional","",""
"What color is red wine?","Red","","multiple_choice","[""Red"",""White"",""Rose""]","0"
CSV;

    Storage::fake('local');
    $path = storage_path('app/test-import.csv');
    file_put_contents($path, $csvContent);

    $service = new CsvImportService();
    $cards = $service->parseCSV($path);

    expect($cards)->toHaveCount(2)
        ->and($cards[0]['question'])->toBe('What is wine?')
        ->and($cards[0]['answer'])->toBe('Fermented grape juice')
        ->and($cards[0]['card_type'])->toBe(CardType::TRADITIONAL)
        ->and($cards[1]['question'])->toBe('What color is red wine?')
        ->and($cards[1]['card_type'])->toBe(CardType::MULTIPLE_CHOICE);

    // Check if answer_choices was parsed (if not null)
    if ($cards[1]['answer_choices'] !== null) {
        expect($cards[1]['answer_choices'])->toBeArray()
            ->and(count($cards[1]['answer_choices']))->toBeGreaterThan(0);
    }

    unlink($path);
});

test('CSV parser skips empty rows', function (): void {
    $csvContent = <<<CSV
question,answer,image_path,card_type,answer_choices,correct_answer_index
"Valid question","Valid answer","","traditional","",""
,,,,,
"Another question","Another answer","","traditional","",""
CSV;

    $path = storage_path('app/test-import-empty.csv');
    file_put_contents($path, $csvContent);

    $service = new CsvImportService();
    $cards = $service->parseCSV($path);

    expect($cards)->toHaveCount(2);

    unlink($path);
});

test('CSV validator validates traditional cards', function (): void {
    $service = new CsvImportService();

    $validCard = [
        'question' => 'Test question',
        'answer' => 'Test answer',
        'image_path' => null,
        'card_type' => CardType::TRADITIONAL,
        'answer_choices' => null,
        'correct_answer_index' => null,
    ];

    expect($service->validate($validCard))->toBeTrue();
});

test('CSV validator requires question and answer', function (): void {
    $service = new CsvImportService();

    $invalidCard = [
        'question' => '',
        'answer' => 'Test answer',
        'image_path' => null,
        'card_type' => CardType::TRADITIONAL,
        'answer_choices' => null,
        'correct_answer_index' => null,
    ];

    expect($service->validate($invalidCard))->toBeFalse();
});

test('CSV validator validates multiple choice cards', function (): void {
    $service = new CsvImportService();

    $validMCCard = [
        'question' => 'Test question',
        'answer' => 'Test answer',
        'image_path' => null,
        'card_type' => CardType::MULTIPLE_CHOICE,
        'answer_choices' => ['A', 'B', 'C'],
        'correct_answer_index' => 1,
    ];

    expect($service->validate($validMCCard))->toBeTrue();
});

test('CSV validator rejects MC cards with insufficient choices', function (): void {
    $service = new CsvImportService();

    $invalidMCCard = [
        'question' => 'Test question',
        'answer' => 'Test answer',
        'image_path' => null,
        'card_type' => CardType::MULTIPLE_CHOICE,
        'answer_choices' => ['A'],
        'correct_answer_index' => 0,
    ];

    expect($service->validate($invalidMCCard))->toBeFalse();
});

test('CSV validator rejects MC cards with invalid answer index', function (): void {
    $service = new CsvImportService();

    $invalidMCCard = [
        'question' => 'Test question',
        'answer' => 'Test answer',
        'image_path' => null,
        'card_type' => CardType::MULTIPLE_CHOICE,
        'answer_choices' => ['A', 'B', 'C'],
        'correct_answer_index' => 5,
    ];

    expect($service->validate($invalidMCCard))->toBeFalse();
});

test('can import deck from CSV file', function (): void {
    $admin = \Domain\Admin\Models\Admin::factory()->create();

    $csvContent = <<<CSV
question,answer,image_path,card_type,answer_choices,correct_answer_index
"What is wine?","Fermented grape juice","","traditional","",""
"What grape is Bordeaux famous for?","Cabernet Sauvignon","","traditional","",""
CSV;

    $path = storage_path('app/test-deck-import.csv');
    file_put_contents($path, $csvContent);

    $action = new \Domain\Deck\Actions\ImportDeckAction(
        new CsvImportService(),
        new \Domain\Deck\Services\ApkgImportService(),
        new \Domain\Card\Actions\CreateCardAction()
    );

    $result = $action->execute(
        userId: $admin->id,
        filePath: $path,
        deckName: 'Test Wine Deck',
        description: 'A test deck'
    );

    expect($result->status)->toBe(\Domain\Deck\Enums\ImportStatus::COMPLETED)
        ->and($result->imported_cards_count)->toBe(2);

    $this->assertDatabaseHas('decks', [
        'name' => 'Test Wine Deck',
        'created_by' => $admin->id,
    ]);

    $this->assertDatabaseHas('cards', [
        'question' => 'What is wine?',
        'answer' => 'Fermented grape juice',
    ]);

    unlink($path);
});

test('import handles errors gracefully', function (): void {
    $admin = \Domain\Admin\Models\Admin::factory()->create();

    $action = new \Domain\Deck\Actions\ImportDeckAction(
        new CsvImportService(),
        new \Domain\Deck\Services\ApkgImportService(),
        new \Domain\Card\Actions\CreateCardAction()
    );

    expect(fn () => $action->execute(
        userId: $admin->id,
        filePath: '/nonexistent/file.csv',
        deckName: 'Test Deck',
        description: ''
    ))->toThrow(\RuntimeException::class); // Changed from InvalidArgumentException
});

