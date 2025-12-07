<?php

declare(strict_types=1);

namespace Domain\Card\Actions;

use Domain\Card\Repositories\CardRepository;

class ExportDeckCardsAction
{
    public function __construct(
        private CardRepository $cardRepository
    ) {}

    /**
     * Export all cards from a deck as CSV content.
     *
     * CSV Format: question,is_multiple_choice,number_of_correct_answers,answer1,answer2,...
     * Correct answers are listed first.
     */
    public function execute(int $deckId): string
    {
        $cards = $this->cardRepository->getByDeckId($deckId);

        $lines = [];

        // Header row
        $lines[] = 'question,is_multiple_choice,number_of_correct_answers,answers...';

        foreach ($cards as $card) {
            $lines[] = $this->formatCardRow($card);
        }

        return implode("\n", $lines);
    }

    /**
     * Format a single card as a CSV row.
     */
    private function formatCardRow(\Domain\Card\Data\CardData $card): string
    {
        $answerChoices = $card->answer_choices ?? [];
        $correctIndices = $card->correct_answer_indices ?? [];

        // Separate correct and incorrect answers
        $correctAnswers = [];
        $incorrectAnswers = [];

        foreach ($answerChoices as $index => $answer) {
            if (in_array($index, $correctIndices, true)) {
                $correctAnswers[] = $answer;
            } else {
                $incorrectAnswers[] = $answer;
            }
        }

        // Combine with correct answers first
        $orderedAnswers = array_merge($correctAnswers, $incorrectAnswers);

        // Build CSV row
        $row = [
            $this->escapeCsvField($card->question),
            $card->is_multi_select ? 'true' : 'false',
            (string) count($correctIndices),
        ];

        // Add each answer as a separate column
        foreach ($orderedAnswers as $answer) {
            $row[] = $this->escapeCsvField($answer);
        }

        return implode(',', $row);
    }

    /**
     * Escape a field for CSV output.
     */
    private function escapeCsvField(string $field): string
    {
        // If field contains comma, quote, or newline, wrap in quotes and escape internal quotes
        if (strpos($field, ',') !== false || strpos($field, '"') !== false || strpos($field, "\n") !== false) {
            return '"' . str_replace('"', '""', $field) . '"';
        }

        return $field;
    }
}

