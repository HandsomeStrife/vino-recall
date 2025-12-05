<?php

declare(strict_types=1);

namespace Domain\Deck\Services;

use Domain\Deck\Enums\ImportFormat;

class ImportValidationService
{
    private const REQUIRED_COLUMNS = ['question', 'answer', 'image_path', 'card_type', 'answer_choices', 'correct_answer_indices'];

    private const MIN_REQUIRED_COLUMNS = 6;

    /**
     * Validate the import file structure and return validation result
     *
     * @return array{valid: bool, errors: array, warnings: array, row_count: int}
     */
    public function validateFile(string $filePath, ImportFormat $format): array
    {
        if (! file_exists($filePath)) {
            return [
                'valid' => false,
                'errors' => ['File not found'],
                'warnings' => [],
                'row_count' => 0,
            ];
        }

        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            return [
                'valid' => false,
                'errors' => ['Unable to open file'],
                'warnings' => [],
                'row_count' => 0,
            ];
        }

        $delimiter = $format === ImportFormat::TXT ? "\t" : ',';
        $errors = [];
        $warnings = [];
        $rowCount = 0;

        // Read and validate header
        $headerLine = $format === ImportFormat::TXT ? fgets($handle) : fgetcsv($handle);
        if ($headerLine === false || $headerLine === null) {
            fclose($handle);

            return [
                'valid' => false,
                'errors' => ['File is empty or has invalid format'],
                'warnings' => [],
                'row_count' => 0,
            ];
        }

        // Parse header based on format
        if ($format === ImportFormat::TXT) {
            $header = explode("\t", trim($headerLine));
        } else {
            $header = $headerLine;
        }

        // Validate column count
        if (count($header) < self::MIN_REQUIRED_COLUMNS) {
            $errors[] = sprintf(
                'Invalid header: expected %d columns (%s), found %d',
                self::MIN_REQUIRED_COLUMNS,
                implode(', ', self::REQUIRED_COLUMNS),
                count($header)
            );
        }

        // Validate rows
        $lineNumber = 1;
        $validRows = 0;
        $invalidRows = [];

        while (true) {
            $lineNumber++;

            if ($format === ImportFormat::TXT) {
                $line = fgets($handle);
                if ($line === false) {
                    break;
                }
                $line = trim($line);
                if (empty($line)) {
                    continue;
                }
                $row = explode("\t", $line);
            } else {
                $row = fgetcsv($handle);
                if ($row === false) {
                    break;
                }
                if (count($row) < 2 || (count($row) === 1 && empty($row[0]))) {
                    continue;
                }
            }

            $rowCount++;
            $rowErrors = $this->validateRow($row, $lineNumber);

            if (empty($rowErrors)) {
                $validRows++;
            } else {
                $invalidRows[$lineNumber] = $rowErrors;
                // Only store first 10 row errors to avoid overwhelming output
                if (count($invalidRows) <= 10) {
                    foreach ($rowErrors as $error) {
                        $errors[] = "Row {$lineNumber}: {$error}";
                    }
                }
            }
        }

        fclose($handle);

        if (count($invalidRows) > 10) {
            $warnings[] = sprintf('Additional %d rows have validation errors (not shown)', count($invalidRows) - 10);
        }

        if ($rowCount === 0) {
            $errors[] = 'No data rows found in file';
        }

        if ($validRows === 0 && $rowCount > 0) {
            $errors[] = 'No valid rows found in file';
        }

        if ($validRows > 0 && count($invalidRows) > 0) {
            $warnings[] = sprintf('%d of %d rows will be skipped due to validation errors', count($invalidRows), $rowCount);
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
            'row_count' => $rowCount,
            'valid_rows' => $validRows,
            'invalid_rows' => count($invalidRows),
        ];
    }

    /**
     * Validate a single row
     *
     * @return array<string> List of errors for this row
     */
    private function validateRow(array $row, int $lineNumber): array
    {
        $errors = [];

        // Check question
        $question = trim($row[0] ?? '');
        if (empty($question)) {
            $errors[] = 'Missing question';
        }

        // Check answer
        $answer = trim($row[1] ?? '');
        if (empty($answer)) {
            $errors[] = 'Missing answer';
        }

        // Validate answer choices (column 5, index 4)
        $answerChoicesJson = $row[4] ?? '';
        if (empty($answerChoicesJson)) {
            $errors[] = 'Missing answer_choices';
        } else {
            $answerChoices = json_decode($answerChoicesJson, true);
            if ($answerChoices === null || ! is_array($answerChoices)) {
                $errors[] = 'Invalid answer_choices JSON format';
            } elseif (count($answerChoices) < 2) {
                $errors[] = 'answer_choices must have at least 2 options';
            }
        }

        // Validate correct answer indices (column 6, index 5)
        $correctIndicesJson = $row[5] ?? '';
        if (empty($correctIndicesJson)) {
            $errors[] = 'Missing correct_answer_indices';
        } else {
            $correctIndices = json_decode($correctIndicesJson, true);
            if ($correctIndices === null && ! is_numeric($correctIndicesJson)) {
                $errors[] = 'Invalid correct_answer_indices JSON format';
            } elseif (is_array($correctIndices)) {
                // Validate indices are within bounds if we have answer choices
                if (! empty($answerChoicesJson)) {
                    $answerChoices = json_decode($answerChoicesJson, true);
                    if (is_array($answerChoices)) {
                        foreach ($correctIndices as $index) {
                            if (! is_int($index) && ! is_numeric($index)) {
                                $errors[] = 'correct_answer_indices must contain integers';
                                break;
                            }
                            if ((int) $index < 0 || (int) $index >= count($answerChoices)) {
                                $errors[] = sprintf('correct_answer_indices contains out-of-bounds index: %d (valid range: 0-%d)', $index, count($answerChoices) - 1);
                                break;
                            }
                        }
                    }
                }
            }
        }

        return $errors;
    }
}

