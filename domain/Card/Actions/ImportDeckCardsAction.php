<?php

declare(strict_types=1);

namespace Domain\Card\Actions;

use Domain\Card\Data\ImportResultData;
use InvalidArgumentException;

class ImportDeckCardsAction
{
    public function __construct(
        private CreateCardAction $createCardAction
    ) {}

    /**
     * Import cards from CSV content into a deck.
     *
     * CSV Format: question,is_multiple_choice,number_of_correct_answers,answer1,answer2,...
     * First N answers (where N = number_of_correct_answers) are correct.
     *
     * @throws InvalidArgumentException
     */
    public function execute(int $deckId, string $csvContent): ImportResultData
    {
        $lines = $this->parseLines($csvContent);

        if (count($lines) < 2) {
            throw new InvalidArgumentException('CSV must contain a header row and at least one data row.');
        }

        // Skip header row
        $dataRows = array_slice($lines, 1);

        $imported = 0;
        $skipped = 0;
        $errors = [];

        foreach ($dataRows as $rowIndex => $row) {
            $lineNumber = $rowIndex + 2; // Account for header and 0-index

            try {
                $this->processRow($deckId, $row, $lineNumber);
                $imported++;
            } catch (InvalidArgumentException $e) {
                $errors[] = "Line {$lineNumber}: {$e->getMessage()}";
                $skipped++;
            }
        }

        return new ImportResultData(
            imported: $imported,
            skipped: $skipped,
            errors: $errors
        );
    }

    /**
     * Parse CSV content into array of rows.
     *
     * @return array<int, array<int, string>>
     */
    private function parseLines(string $csvContent): array
    {
        $lines = [];
        $handle = fopen('php://temp', 'r+');

        if ($handle === false) {
            throw new InvalidArgumentException('Failed to parse CSV content.');
        }

        fwrite($handle, $csvContent);
        rewind($handle);

        while (($row = fgetcsv($handle)) !== false) {
            // Skip empty rows
            if (count($row) === 1 && $row[0] === null) {
                continue;
            }
            $lines[] = $row;
        }

        fclose($handle);

        return $lines;
    }

    /**
     * Process a single CSV row and create a card.
     *
     * @param  array<int, string>  $row
     *
     * @throws InvalidArgumentException
     */
    private function processRow(int $deckId, array $row, int $lineNumber): void
    {
        // Minimum required columns: question, is_multiple_choice, number_of_correct_answers, answer1, answer2
        if (count($row) < 5) {
            throw new InvalidArgumentException('Row must have at least 5 columns (question, is_multiple_choice, number_of_correct_answers, and at least 2 answers).');
        }

        $question = trim($row[0] ?? '');
        $isMultipleChoice = strtolower(trim($row[1] ?? '')) === 'true';
        $numberOfCorrectAnswers = (int) trim($row[2] ?? '0');

        // Extract all answers (from column 3 onwards)
        $answers = [];
        for ($i = 3; $i < count($row); $i++) {
            $answer = trim($row[$i]);
            if ($answer !== '') {
                $answers[] = $answer;
            }
        }

        // Validate question
        if ($question === '') {
            throw new InvalidArgumentException('Question cannot be empty.');
        }

        // Validate answers
        if (count($answers) < 2) {
            throw new InvalidArgumentException('Must have at least 2 non-empty answers.');
        }

        // Validate number of correct answers
        if ($numberOfCorrectAnswers < 1) {
            throw new InvalidArgumentException('Must have at least 1 correct answer.');
        }

        if ($numberOfCorrectAnswers > count($answers)) {
            throw new InvalidArgumentException("Number of correct answers ({$numberOfCorrectAnswers}) exceeds total answers (".count($answers).').');
        }

        // First N answers are correct, build indices array
        $correctIndices = [];
        for ($i = 0; $i < $numberOfCorrectAnswers; $i++) {
            $correctIndices[] = $i;
        }

        // Build answer string from correct answers
        $correctAnswerTexts = array_slice($answers, 0, $numberOfCorrectAnswers);
        $answerString = implode(', ', $correctAnswerTexts);

        // Determine if multi-select (more than one correct answer means multi-select)
        $isMultiSelect = $isMultipleChoice && $numberOfCorrectAnswers > 1;

        $this->createCardAction->execute(
            deckId: $deckId,
            question: $question,
            answer: $answerString,
            image_path: null,
            answerChoices: $answers,
            correctAnswerIndices: $correctIndices,
            isMultiSelect: $isMultiSelect
        );
    }
}
