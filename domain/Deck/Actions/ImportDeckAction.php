<?php

declare(strict_types=1);

namespace Domain\Deck\Actions;

use Domain\Card\Actions\CreateCardAction;
use Domain\Deck\Data\DeckImportData;
use Domain\Deck\Enums\ImportFormat;
use Domain\Deck\Enums\ImportStatus;
use Domain\Deck\Models\Deck;
use Domain\Deck\Models\DeckImport;
use Domain\Deck\Services\ApkgImportService;
use Domain\Deck\Services\CsvImportService;

class ImportDeckAction
{
    public function __construct(
        private CsvImportService $csvImportService,
        private ApkgImportService $apkgImportService,
        private CreateCardAction $createCardAction
    ) {}

    /**
     * Execute the deck import
     */
    public function execute(
        int $userId,
        string $filePath,
        string $deckName,
        string $description = '',
        ?ImportFormat $format = null
    ): DeckImportData {
        // Determine format from file extension if not provided
        if ($format === null) {
            $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            $format = match ($extension) {
                'apkg' => ImportFormat::APKG,
                'csv' => ImportFormat::CSV,
                default => throw new \InvalidArgumentException("Unsupported file format: {$extension}"),
            };
        }

        // Create import record
        $import = DeckImport::create([
            'user_id' => $userId,
            'filename' => basename($filePath),
            'format' => $format->value,
            'status' => ImportStatus::PROCESSING->value,
        ]);

        try {
            // Parse cards based on format
            $cardsData = match ($format) {
                ImportFormat::CSV => $this->csvImportService->parseCSV($filePath),
                ImportFormat::APKG => $this->apkgImportService->parseAPKG($filePath),
            };

            if (empty($cardsData)) {
                throw new \RuntimeException('No valid cards found in import file');
            }

            // Wrap deck creation and card imports in a transaction
            $deck = \DB::transaction(function () use ($deckName, $description, $userId, $cardsData, $format) {
                // Create deck
                $deck = Deck::create([
                    'name' => $deckName,
                    'description' => $description,
                    'is_active' => true,
                    'created_by' => $userId,
                ]);

                // Import cards
                $importedCount = 0;
                foreach ($cardsData as $cardData) {
                    // Validate card data
                    $validator = $format === ImportFormat::CSV
                        ? $this->csvImportService
                        : $this->apkgImportService;

                    if (! $validator->validate($cardData)) {
                        continue; // Skip invalid cards
                    }

                    try {
                        $this->createCardAction->execute(
                            deckId: $deck->id,
                            question: $cardData['question'],
                            answer: $cardData['answer'],
                            image_path: $cardData['image_path'],
                            cardType: $cardData['card_type'],
                            answerChoices: $cardData['answer_choices'],
                            correctAnswerIndex: $cardData['correct_answer_index']
                        );
                        $importedCount++;
                    } catch (\Exception $e) {
                        // Log error but continue with other cards
                        \Log::warning("Failed to import card: {$e->getMessage()}", [
                            'deck_id' => $deck->id,
                            'card_data' => $cardData,
                        ]);
                    }
                }

                // Store imported count for later update
                $deck->imported_count = $importedCount;

                return $deck;
            });

            // Update import record
            $import->update([
                'deck_id' => $deck->id,
                'status' => ImportStatus::COMPLETED->value,
                'imported_cards_count' => $deck->imported_count,
            ]);
        } catch (\Exception $e) {
            // Update import record with error
            $import->update([
                'status' => ImportStatus::FAILED->value,
                'error_message' => 'Import processing failed. Please check your file format.',
            ]);

            // Log detailed error for admin review
            \Log::error("Deck import failed", [
                'import_id' => $import->id,
                'user_id' => $userId,
                'filename' => basename($filePath),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw new \RuntimeException('Import failed. Please check your file format and try again.');
        }

        return DeckImportData::fromModel($import->fresh());
    }
}

