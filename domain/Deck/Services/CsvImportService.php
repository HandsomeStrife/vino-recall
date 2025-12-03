<?php

declare(strict_types=1);

namespace Domain\Deck\Services;

use Domain\Card\Enums\CardType;

class CsvImportService
{
    private const MAX_QUESTION_LENGTH = 1000;

    private const MAX_ANSWER_LENGTH = 5000;

    private const ALLOWED_IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    /**
     * Parse CSV file and return cards data
     *
     * Expected format: question,answer,image_path,card_type,[answer_choices],[correct_answer_index]
     */
    public function parseCSV(string $filePath): array
    {
        if (! file_exists($filePath)) {
            throw new \InvalidArgumentException("File not found: {$filePath}");
        }

        $cards = [];
        $handle = fopen($filePath, 'r');

        if ($handle === false) {
            throw new \RuntimeException("Unable to open file: {$filePath}");
        }

        // Skip header row
        $header = fgetcsv($handle);

        while (($row = fgetcsv($handle)) !== false) {
            // Skip empty rows
            if (count($row) < 2) {
                continue;
            }

            $question = $this->sanitizeCsvValue($row[0] ?? '');
            $answer = $this->sanitizeCsvValue($row[1] ?? '');
            $imagePath = $this->sanitizeImagePath($row[2] ?? null);
            $cardTypeStr = $row[3] ?? 'traditional';
            $answerChoicesJson = $row[4] ?? null;
            $correctAnswerIndex = isset($row[5]) && $row[5] !== '' ? (int) $row[5] : null;

            if (empty($question) || empty($answer)) {
                continue;
            }

            // Enforce length limits
            $question = substr($question, 0, self::MAX_QUESTION_LENGTH);
            $answer = substr($answer, 0, self::MAX_ANSWER_LENGTH);

            $cardType = $cardTypeStr === 'multiple_choice' ? CardType::MULTIPLE_CHOICE : CardType::TRADITIONAL;
            $answerChoices = null;

            if ($cardType === CardType::MULTIPLE_CHOICE && $answerChoicesJson) {
                $answerChoices = json_decode($answerChoicesJson, true);
                if (! is_array($answerChoices)) {
                    $answerChoices = null;
                } else {
                    // Sanitize each answer choice
                    $answerChoices = array_map(fn ($choice) => $this->sanitizeCsvValue($choice), $answerChoices);
                }
            }

            $cards[] = [
                'question' => $question,
                'answer' => $answer,
                'image_path' => $imagePath,
                'card_type' => $cardType,
                'answer_choices' => $answerChoices,
                'correct_answer_index' => $correctAnswerIndex,
            ];
        }

        fclose($handle);

        return $cards;
    }

    /**
     * Sanitize CSV value to prevent formula injection
     */
    private function sanitizeCsvValue(string $value): string
    {
        $value = trim($value);

        // Prevent CSV formula injection
        if (str_starts_with($value, '=') ||
            str_starts_with($value, '+') ||
            str_starts_with($value, '-') ||
            str_starts_with($value, '@') ||
            str_starts_with($value, "\t") ||
            str_starts_with($value, "\r")) {
            $value = "'" . $value; // Prefix with quote to neutralize
        }

        return $value;
    }

    /**
     * Sanitize and validate image path
     */
    private function sanitizeImagePath(?string $imagePath): ?string
    {
        if (empty($imagePath) || $imagePath === '') {
            return null;
        }

        // Remove any leading/trailing whitespace
        $imagePath = trim($imagePath);

        // Reject absolute paths
        if (str_starts_with($imagePath, '/') || preg_match('/^[a-zA-Z]:\\\\/', $imagePath)) {
            return null;
        }

        // Reject path traversal attempts
        if (str_contains($imagePath, '..') || str_contains($imagePath, '\\')) {
            return null;
        }

        // Reject URLs
        if (str_starts_with($imagePath, 'http://') || str_starts_with($imagePath, 'https://')) {
            return null;
        }

        // Validate file extension
        $ext = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));
        if (! in_array($ext, self::ALLOWED_IMAGE_EXTENSIONS)) {
            return null;
        }

        // Ensure path doesn't contain null bytes
        if (str_contains($imagePath, "\0")) {
            return null;
        }

        return $imagePath;
    }

    /**
     * Validate card data
     */
    public function validate(array $cardData): bool
    {
        if (empty($cardData['question']) || empty($cardData['answer'])) {
            return false;
        }

        if ($cardData['card_type'] === CardType::MULTIPLE_CHOICE) {
            if (empty($cardData['answer_choices']) || ! is_array($cardData['answer_choices'])) {
                return false;
            }

            if (count($cardData['answer_choices']) < 2) {
                return false;
            }

            if ($cardData['correct_answer_index'] === null ||
                $cardData['correct_answer_index'] < 0 ||
                $cardData['correct_answer_index'] >= count($cardData['answer_choices'])) {
                return false;
            }
        }

        return true;
    }
}

