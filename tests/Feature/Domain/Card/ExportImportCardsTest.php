<?php

declare(strict_types=1);

use Domain\Card\Actions\ExportDeckCardsAction;
use Domain\Card\Actions\ImportDeckCardsAction;
use Domain\Card\Models\Card;
use Domain\Card\Repositories\CardRepository;
use Domain\Deck\Models\Deck;

test('export deck cards action generates csv with correct format', function () {
    $deck = Deck::factory()->create();

    // Create a card with single correct answer
    Card::factory()->create([
        'deck_id' => $deck->id,
        'question' => 'What is the capital of France?',
        'answer' => 'Paris',
        'answer_choices' => json_encode(['Paris', 'London', 'Berlin', 'Rome']),
        'correct_answer_indices' => json_encode([0]),
        'is_multi_select' => false,
    ]);

    $action = new ExportDeckCardsAction(new CardRepository);
    $csv = $action->execute($deck->id);

    // Verify header
    expect($csv)->toContain('question,is_multiple_choice,number_of_correct_answers,answers...');

    // Verify data row - correct answer (Paris) should be first
    expect($csv)->toContain('What is the capital of France?');
    expect($csv)->toContain('false'); // not multi-select
    expect($csv)->toContain(',1,'); // 1 correct answer
});

test('export deck cards action places correct answers first', function () {
    $deck = Deck::factory()->create();

    // Create a card where correct answer is at index 2 (Berlin)
    Card::factory()->create([
        'deck_id' => $deck->id,
        'question' => 'Which city is in Germany?',
        'answer' => 'Berlin',
        'answer_choices' => json_encode(['Paris', 'London', 'Berlin', 'Rome']),
        'correct_answer_indices' => json_encode([2]),
        'is_multi_select' => false,
    ]);

    $action = new ExportDeckCardsAction(new CardRepository);
    $csv = $action->execute($deck->id);

    // Parse CSV to verify order
    $lines = explode("\n", $csv);
    $dataLine = $lines[1];

    // Berlin should come before Paris/London/Rome in the exported CSV
    $berlinPos = strpos($dataLine, 'Berlin');
    $parisPos = strpos($dataLine, 'Paris');

    expect($berlinPos)->toBeLessThan($parisPos);
});

test('export deck cards action handles multiple correct answers', function () {
    $deck = Deck::factory()->create();

    Card::factory()->create([
        'deck_id' => $deck->id,
        'question' => 'Which are red grape varieties?',
        'answer' => 'Merlot, Syrah',
        'answer_choices' => json_encode(['Chardonnay', 'Merlot', 'Riesling', 'Syrah']),
        'correct_answer_indices' => json_encode([1, 3]),
        'is_multi_select' => true,
    ]);

    $action = new ExportDeckCardsAction(new CardRepository);
    $csv = $action->execute($deck->id);

    expect($csv)->toContain('true'); // is multi-select
    expect($csv)->toContain(',2,'); // 2 correct answers
});

test('export deck cards action handles empty deck', function () {
    $deck = Deck::factory()->create();

    $action = new ExportDeckCardsAction(new CardRepository);
    $csv = $action->execute($deck->id);

    // Should only contain header
    $lines = array_filter(explode("\n", $csv));
    expect(count($lines))->toBe(1);
});

test('export deck cards action escapes csv special characters', function () {
    $deck = Deck::factory()->create();

    Card::factory()->create([
        'deck_id' => $deck->id,
        'question' => 'What is "AOC", also known as "AOP"?',
        'answer' => 'Appellation d\'Origine Controlee',
        'answer_choices' => json_encode(['Appellation d\'Origine Controlee', 'Something, else', 'Option 3', 'Option 4']),
        'correct_answer_indices' => json_encode([0]),
        'is_multi_select' => false,
    ]);

    $action = new ExportDeckCardsAction(new CardRepository);
    $csv = $action->execute($deck->id);

    // Verify quotes are properly escaped
    expect($csv)->toContain('""AOC""');
    // Verify field with comma is quoted
    expect($csv)->toContain('"Something, else"');
});

test('import deck cards action creates cards from csv', function () {
    $deck = Deck::factory()->create();

    $csv = <<<'CSV'
question,is_multiple_choice,number_of_correct_answers,answer1,answer2,answer3,answer4
"What is the capital of France?",true,1,Paris,London,Berlin,Rome
CSV;

    $action = new ImportDeckCardsAction(new \Domain\Card\Actions\CreateCardAction);
    $result = $action->execute($deck->id, $csv);

    expect($result->imported)->toBe(1);
    expect($result->skipped)->toBe(0);
    expect($result->errors)->toBeEmpty();

    // Verify card was created
    $card = Card::where('deck_id', $deck->id)->first();
    expect($card)->not->toBeNull();
    expect($card->question)->toBe('What is the capital of France?');

    $answerChoices = json_decode($card->answer_choices, true);
    expect($answerChoices)->toHaveCount(4);
    expect($answerChoices[0])->toBe('Paris'); // First answer is correct

    $correctIndices = json_decode($card->correct_answer_indices, true);
    expect($correctIndices)->toBe([0]); // Index 0 is correct
});

test('import deck cards action handles multiple correct answers', function () {
    $deck = Deck::factory()->create();

    $csv = <<<'CSV'
question,is_multiple_choice,number_of_correct_answers,answer1,answer2,answer3,answer4
"Which are red grapes?",true,2,Merlot,Syrah,Chardonnay,Riesling
CSV;

    $action = new ImportDeckCardsAction(new \Domain\Card\Actions\CreateCardAction);
    $result = $action->execute($deck->id, $csv);

    expect($result->imported)->toBe(1);

    $card = Card::where('deck_id', $deck->id)->first();
    $correctIndices = json_decode($card->correct_answer_indices, true);
    expect($correctIndices)->toBe([0, 1]); // First 2 answers are correct
    expect($card->is_multi_select)->toBeTrue();
});

test('import deck cards action imports multiple cards', function () {
    $deck = Deck::factory()->create();

    $csv = <<<'CSV'
question,is_multiple_choice,number_of_correct_answers,answer1,answer2,answer3,answer4
"Question 1?",true,1,A,B,C,D
"Question 2?",true,1,E,F,G,H
"Question 3?",true,2,I,J,K,L
CSV;

    $action = new ImportDeckCardsAction(new \Domain\Card\Actions\CreateCardAction);
    $result = $action->execute($deck->id, $csv);

    expect($result->imported)->toBe(3);
    expect(Card::where('deck_id', $deck->id)->count())->toBe(3);
});

test('import deck cards action skips invalid rows and reports errors', function () {
    $deck = Deck::factory()->create();

    $csv = <<<'CSV'
question,is_multiple_choice,number_of_correct_answers,answer1,answer2,answer3,answer4
"Valid question?",true,1,A,B,C,D
"",true,1,A,B,C,D
"Only one answer?",true,1,A
CSV;

    $action = new ImportDeckCardsAction(new \Domain\Card\Actions\CreateCardAction);
    $result = $action->execute($deck->id, $csv);

    expect($result->imported)->toBe(1);
    expect($result->skipped)->toBe(2);
    expect($result->errors)->toHaveCount(2);
});

test('import deck cards action validates minimum answers', function () {
    $deck = Deck::factory()->create();

    $csv = <<<'CSV'
question,is_multiple_choice,number_of_correct_answers,answer1
"Only one answer?",true,1,OnlyAnswer
CSV;

    $action = new ImportDeckCardsAction(new \Domain\Card\Actions\CreateCardAction);
    $result = $action->execute($deck->id, $csv);

    expect($result->imported)->toBe(0);
    expect($result->skipped)->toBe(1);
    expect($result->errors[0])->toContain('at least 2');
});

test('import deck cards action validates correct answer count', function () {
    $deck = Deck::factory()->create();

    $csv = <<<'CSV'
question,is_multiple_choice,number_of_correct_answers,answer1,answer2
"Too many correct?",true,5,A,B
CSV;

    $action = new ImportDeckCardsAction(new \Domain\Card\Actions\CreateCardAction);
    $result = $action->execute($deck->id, $csv);

    expect($result->imported)->toBe(0);
    expect($result->skipped)->toBe(1);
    expect($result->errors[0])->toContain('exceeds');
});

test('import deck cards action requires at least one correct answer', function () {
    $deck = Deck::factory()->create();

    $csv = <<<'CSV'
question,is_multiple_choice,number_of_correct_answers,answer1,answer2
"Zero correct?",true,0,A,B
CSV;

    $action = new ImportDeckCardsAction(new \Domain\Card\Actions\CreateCardAction);
    $result = $action->execute($deck->id, $csv);

    expect($result->imported)->toBe(0);
    expect($result->skipped)->toBe(1);
    expect($result->errors[0])->toContain('at least 1 correct');
});

test('import deck cards action throws exception for empty csv', function () {
    $deck = Deck::factory()->create();

    $action = new ImportDeckCardsAction(new \Domain\Card\Actions\CreateCardAction);

    expect(fn () => $action->execute($deck->id, ''))
        ->toThrow(\InvalidArgumentException::class);
});

test('import deck cards action throws exception for header only csv', function () {
    $deck = Deck::factory()->create();

    $csv = 'question,is_multiple_choice,number_of_correct_answers,answer1,answer2';

    $action = new ImportDeckCardsAction(new \Domain\Card\Actions\CreateCardAction);

    expect(fn () => $action->execute($deck->id, $csv))
        ->toThrow(\InvalidArgumentException::class, 'at least one data row');
});

test('export and import roundtrip preserves card data', function () {
    $deck = Deck::factory()->create();

    // Create original cards
    Card::factory()->create([
        'deck_id' => $deck->id,
        'question' => 'What is the capital of France?',
        'answer' => 'Paris',
        'answer_choices' => json_encode(['Paris', 'London', 'Berlin', 'Rome']),
        'correct_answer_indices' => json_encode([0]),
        'is_multi_select' => false,
    ]);

    Card::factory()->create([
        'deck_id' => $deck->id,
        'question' => 'Which are red grapes?',
        'answer' => 'Merlot, Syrah',
        'answer_choices' => json_encode(['Merlot', 'Syrah', 'Chardonnay', 'Riesling']),
        'correct_answer_indices' => json_encode([0, 1]),
        'is_multi_select' => true,
    ]);

    // Export
    $exportAction = new ExportDeckCardsAction(new CardRepository);
    $csv = $exportAction->execute($deck->id);

    // Create new deck and import
    $newDeck = Deck::factory()->create();
    $importAction = new ImportDeckCardsAction(new \Domain\Card\Actions\CreateCardAction);
    $result = $importAction->execute($newDeck->id, $csv);

    expect($result->imported)->toBe(2);

    // Verify imported cards match original questions
    $importedCards = Card::where('deck_id', $newDeck->id)->get();
    expect($importedCards)->toHaveCount(2);

    $questions = $importedCards->pluck('question')->toArray();
    expect($questions)->toContain('What is the capital of France?');
    expect($questions)->toContain('Which are red grapes?');
});
