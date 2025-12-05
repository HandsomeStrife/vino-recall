<?php

declare(strict_types=1);

namespace Domain\Deck\Jobs;

use Domain\Card\Actions\CreateCardAction;
use Domain\Card\Actions\UpdateCardAction;
use Domain\Card\Models\Card;
use Domain\Deck\Enums\ImportFormat;
use Domain\Deck\Enums\ImportStatus;
use Domain\Deck\Models\Deck;
use Domain\Deck\Models\DeckImport;
use Domain\Deck\Services\CsvImportService;
use Domain\Deck\Services\TxtImportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessDeckImportJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 1;

    public int $timeout = 300; // 5 minutes

    public function __construct(
        private int $importId
    ) {}

    public function handle(
        CsvImportService $csvImportService,
        TxtImportService $txtImportService,
        CreateCardAction $createCardAction,
        UpdateCardAction $updateCardAction
    ): void {
        $import = DeckImport::find($this->importId);

        if (! $import) {
            Log::error("DeckImport not found: {$this->importId}");

            return;
        }

        // Mark as processing
        $import->update([
            'status' => ImportStatus::PROCESSING->value,
            'started_at' => now(),
        ]);

        try {
            $filePath = storage_path('app/' . $import->file_path);

            if (! file_exists($filePath)) {
                throw new \RuntimeException("Import file not found: {$filePath}");
            }

            // Parse cards based on format
            $cardsData = match ($import->format) {
                ImportFormat::CSV => $csvImportService->parseCSV($filePath),
                ImportFormat::TXT => $txtImportService->parseTXT($filePath),
            };

            if (empty($cardsData)) {
                throw new \RuntimeException('No valid cards found in import file');
            }

            // Get or create deck
            $deck = $import->deck_id
                ? Deck::findOrFail($import->deck_id)
                : null;

            // Process import within a transaction
            DB::transaction(function () use ($import, $deck, $cardsData, $createCardAction, $updateCardAction, $csvImportService, $txtImportService) {
                // Create deck if not specified
                if (! $deck) {
                    $deck = Deck::create([
                        'name' => $import->original_filename ?? 'Imported Deck',
                        'description' => '',
                        'is_active' => true,
                        'created_by' => $import->user_id,
                    ]);

                    $import->deck_id = $deck->id;
                }

                // Get existing cards for this deck (for upsert matching)
                $existingCards = Card::where('deck_id', $deck->id)
                    ->pluck('id', 'question')
                    ->toArray();

                $importedCount = 0;
                $updatedCount = 0;
                $skippedCount = 0;
                $validationErrors = [];

                foreach ($cardsData as $index => $cardData) {
                    // Get validator based on format
                    $validator = match ($import->format) {
                        ImportFormat::CSV => $csvImportService,
                        ImportFormat::TXT => $txtImportService,
                    };

                    // Validate card
                    if (! $validator->validate($cardData)) {
                        $skippedCount++;
                        $lineNumber = $cardData['line_number'] ?? ($index + 2);
                        $validationErrors[] = "Row {$lineNumber}: Invalid card data (missing or invalid answer choices/indices)";

                        continue;
                    }

                    try {
                        // Check if card with same question exists
                        $existingCardId = $existingCards[$cardData['question']] ?? null;

                        if ($existingCardId) {
                            // Update existing card
                            $updateCardAction->execute(
                                cardId: $existingCardId,
                                deckId: $deck->id,
                                question: $cardData['question'],
                                answer: $cardData['answer'],
                                image_path: $cardData['image_path'],
                                cardType: $cardData['card_type'],
                                answerChoices: $cardData['answer_choices'],
                                correctAnswerIndices: $cardData['correct_answer_indices']
                            );
                            $updatedCount++;
                        } else {
                            // Create new card
                            $createCardAction->execute(
                                deckId: $deck->id,
                                question: $cardData['question'],
                                answer: $cardData['answer'],
                                image_path: $cardData['image_path'],
                                cardType: $cardData['card_type'],
                                answerChoices: $cardData['answer_choices'],
                                correctAnswerIndices: $cardData['correct_answer_indices']
                            );
                            $importedCount++;

                            // Add to existing cards map for subsequent duplicate detection
                            $existingCards[$cardData['question']] = true;
                        }
                    } catch (\Exception $e) {
                        $skippedCount++;
                        $lineNumber = $cardData['line_number'] ?? ($index + 2);
                        $validationErrors[] = "Row {$lineNumber}: {$e->getMessage()}";
                        Log::warning("Failed to import card", [
                            'deck_id' => $deck->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }

                // Update import record
                $import->update([
                    'deck_id' => $deck->id,
                    'total_rows' => count($cardsData),
                    'imported_cards_count' => $importedCount,
                    'updated_cards_count' => $updatedCount,
                    'skipped_rows' => $skippedCount,
                    'validation_errors' => ! empty($validationErrors) ? array_slice($validationErrors, 0, 50) : null,
                    'status' => ImportStatus::COMPLETED->value,
                    'completed_at' => now(),
                ]);
            });

            // Clean up import file
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        } catch (\Exception $e) {
            Log::error("Deck import job failed", [
                'import_id' => $this->importId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $import->update([
                'status' => ImportStatus::FAILED->value,
                'error_message' => $e->getMessage(),
                'completed_at' => now(),
            ]);

            // Clean up import file on failure
            $filePath = storage_path('app/' . $import->file_path);
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("Deck import job failed with exception", [
            'import_id' => $this->importId,
            'error' => $exception->getMessage(),
        ]);

        $import = DeckImport::find($this->importId);
        if ($import) {
            $import->update([
                'status' => ImportStatus::FAILED->value,
                'error_message' => 'Import processing failed unexpectedly. Please try again.',
                'completed_at' => now(),
            ]);

            // Clean up import file
            $filePath = storage_path('app/' . $import->file_path);
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
    }
}
