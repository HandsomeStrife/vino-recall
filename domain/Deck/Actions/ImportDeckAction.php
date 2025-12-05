<?php

declare(strict_types=1);

namespace Domain\Deck\Actions;

use Domain\Deck\Data\DeckImportData;
use Domain\Deck\Enums\ImportFormat;
use Domain\Deck\Enums\ImportStatus;
use Domain\Deck\Jobs\ProcessDeckImportJob;
use Domain\Deck\Models\DeckImport;
use Domain\Deck\Services\ImportValidationService;
use Illuminate\Support\Str;

class ImportDeckAction
{
    public function __construct(
        private ImportValidationService $validationService
    ) {}

    /**
     * Start a deck import process
     *
     * @param int $userId The admin user ID initiating the import
     * @param string $filePath The temporary file path of the uploaded file
     * @param string $originalFilename The original filename from the upload
     * @param ImportFormat $format The import format (CSV or TXT)
     * @param int|null $deckId Optional existing deck ID to import into
     * @param string|null $deckName Name for new deck (required if deckId is null)
     * @param string $description Optional description for new deck
     *
     * @return DeckImportData The import record data
     */
    public function execute(
        int $userId,
        string $filePath,
        string $originalFilename,
        ImportFormat $format,
        ?int $deckId = null,
        ?string $deckName = null,
        string $description = ''
    ): DeckImportData {
        // Validate file structure first
        $validation = $this->validationService->validateFile($filePath, $format);

        if (! $validation['valid']) {
            throw new \InvalidArgumentException(
                'File validation failed: ' . implode('; ', $validation['errors'])
            );
        }

        // Generate unique storage filename
        $extension = $format->extension();
        $storedFilename = Str::random(40) . '.' . $extension;
        $storedPath = 'imports/' . $storedFilename;

        // Move file to permanent storage location
        $fullStoredPath = storage_path('app/' . $storedPath);
        $storageDir = dirname($fullStoredPath);
        if (! is_dir($storageDir)) {
            mkdir($storageDir, 0755, true);
        }

        if (! copy($filePath, $fullStoredPath)) {
            throw new \RuntimeException('Failed to store import file');
        }

        // Create import record with pending status
        $import = DeckImport::create([
            'user_id' => $userId,
            'deck_id' => $deckId,
            'filename' => $storedFilename,
            'original_filename' => $originalFilename,
            'file_path' => $storedPath,
            'format' => $format->value,
            'status' => ImportStatus::PENDING->value,
            'total_rows' => $validation['row_count'] ?? 0,
            'validation_errors' => ! empty($validation['warnings']) ? $validation['warnings'] : null,
        ]);

        // If creating a new deck, store the name in the import record
        if (! $deckId && $deckName) {
            $import->update(['original_filename' => $deckName]);
        }

        // Dispatch job for async processing
        ProcessDeckImportJob::dispatch($import->id);

        return DeckImportData::fromModel($import);
    }

    /**
     * Validate a file before import (for preview purposes)
     *
     * @return array{valid: bool, errors: array, warnings: array, row_count: int}
     */
    public function validateFile(string $filePath, ImportFormat $format): array
    {
        return $this->validationService->validateFile($filePath, $format);
    }
}
