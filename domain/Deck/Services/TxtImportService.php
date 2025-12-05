<?php

declare(strict_types=1);

namespace Domain\Deck\Services;

use Domain\Card\Enums\CardType;

class TxtImportService
{
    private const MAX_QUESTION_LENGTH = 1000;

    private const MAX_ANSWER_LENGTH = 5000;

    private const ALLOWED_IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    /**
     * Parse TXT file (tab-delimited) and return cards data
     *
     * Expected format: question\tanswer\timage_path\tcard_type\t[answer_choices]\t[correct_answer_indices]
     */
    public function parseTXT(string $filePath): array
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
        $header = fgets($handle);

        $lineNumber = 1;
        while (($line = fgets($handle)) !== false) {
            $lineNumber++;

            // Skip empty lines
            $line = trim($line);
            if (empty($line)) {
                continue;
            }

            // Split by tab
            $row = explode("\t", $line);

            // Skip rows with less than 2 columns
            if (count($row) < 2) {
                continue;
            }

            $question = $this->sanitizeValue($row[0] ?? '');
            $answer = $this->sanitizeValue($row[1] ?? '');
            $imagePath = $this->sanitizeImagePath($row[2] ?? null);
            // card_type column is ignored - all cards are multiple_choice
            $answerChoicesJson = $row[4] ?? null;
            $correctAnswerIndicesRaw = $row[5] ?? null;

            if (empty($question) || empty($answer)) {
                continue;
            }

            // Enforce length limits
            $question = substr($question, 0, self::MAX_QUESTION_LENGTH);
            $answer = substr($answer, 0, self::MAX_ANSWER_LENGTH);

            $answerChoices = null;
            $correctAnswerIndices = null;

            if ($answerChoicesJson) {
                $answerChoices = json_decode($answerChoicesJson, true);
                if (! is_array($answerChoices)) {
                    $answerChoices = null;
                } else {
                    // Sanitize each answer choice
                    $answerChoices = array_map(fn ($choice) => $this->sanitizeValue($choice), $answerChoices);
                }

                // Parse correct answer indices
                if ($correctAnswerIndicesRaw !== null && $correctAnswerIndicesRaw !== '') {
                    // Try parsing as JSON array first (e.g., "[0,2]")
                    $parsedIndices = json_decode($correctAnswerIndicesRaw, true);
                    if (is_array($parsedIndices)) {
                        $correctAnswerIndices = array_map('intval', $parsedIndices);
                    } elseif (is_numeric($correctAnswerIndicesRaw)) {
                        // Fallback: single integer for backward compatibility
                        $correctAnswerIndices = [(int) $correctAnswerIndicesRaw];
                    }
                }
            }

            $cards[] = [
                'question' => $question,
                'answer' => $answer,
                'image_path' => $imagePath,
                'card_type' => CardType::MULTIPLE_CHOICE,
                'answer_choices' => $answerChoices,
                'correct_answer_indices' => $correctAnswerIndices,
                'line_number' => $lineNumber,
            ];
        }

        fclose($handle);

        return $cards;
    }

    /**
     * Sanitize value to prevent injection
     */
    private function sanitizeValue(string $value): string
    {
        $value = trim($value);

        // Prevent formula injection
        if (str_starts_with($value, '=') ||
            str_starts_with($value, '+') ||
            str_starts_with($value, '-') ||
            str_starts_with($value, '@') ||
            str_starts_with($value, "\t") ||
            str_starts_with($value, "\r")) {
            $value = "'" . $value;
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
     * Validate card data - all cards must be valid multiple choice
     */
    public function validate(array $cardData): bool
    {
        if (empty($cardData['question']) || empty($cardData['answer'])) {
            return false;
        }

        // All cards must have valid multiple choice data
        if (empty($cardData['answer_choices']) || ! is_array($cardData['answer_choices'])) {
            return false;
        }

        if (count($cardData['answer_choices']) < 2) {
            return false;
        }

        if (empty($cardData['correct_answer_indices']) || ! is_array($cardData['correct_answer_indices'])) {
            return false;
        }

        // Validate all indices are within bounds
        foreach ($cardData['correct_answer_indices'] as $index) {
            if ($index < 0 || $index >= count($cardData['answer_choices'])) {
                return false;
            }
        }

        return true;
    }
}

