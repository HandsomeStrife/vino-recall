<?php

declare(strict_types=1);

namespace Domain\Deck\Services;

class ApkgImportService
{
    /**
     * Parse Anki .apkg file and return cards data
     *
     * Note: APKG imports are not supported because all VinoRecall cards must be
     * multiple choice format with answer_choices and correct_answer_indices.
     * Anki cards are free-form Q&A and cannot be automatically converted.
     *
     * Users should convert their Anki decks to CSV format with multiple choice
     * questions before importing.
     */
    public function parseAPKG(string $filePath): array
    {
        throw new \RuntimeException(
            'APKG imports are not supported. VinoRecall requires all cards to be multiple choice format. ' .
            'Please convert your Anki deck to CSV format with answer_choices and correct_answer_indices columns.'
        );
    }

    /**
     * Validate card data - APKG imports are not supported
     */
    public function validate(array $cardData): bool
    {
        return false;
    }
}
