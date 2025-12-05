<?php

declare(strict_types=1);

namespace Domain\Deck\Data;

use Domain\Deck\Enums\ImportFormat;
use Domain\Deck\Enums\ImportStatus;
use Domain\Deck\Models\DeckImport;
use Spatie\LaravelData\Data;

class DeckImportData extends Data
{
    public function __construct(
        public int $id,
        public int $user_id,
        public ?int $deck_id,
        public string $filename,
        public ?string $original_filename,
        public ImportFormat $format,
        public ImportStatus $status,
        public int $imported_cards_count,
        public int $updated_cards_count,
        public int $total_rows,
        public int $skipped_rows,
        public ?string $error_message,
        public ?array $validation_errors,
        public ?string $started_at,
        public ?string $completed_at,
        public string $created_at,
        public string $updated_at,
    ) {}

    public static function fromModel(DeckImport $import): self
    {
        return new self(
            id: $import->id,
            user_id: $import->user_id,
            deck_id: $import->deck_id,
            filename: $import->filename,
            original_filename: $import->original_filename,
            format: $import->format,
            status: $import->status,
            imported_cards_count: $import->imported_cards_count ?? 0,
            updated_cards_count: $import->updated_cards_count ?? 0,
            total_rows: $import->total_rows ?? 0,
            skipped_rows: $import->skipped_rows ?? 0,
            error_message: $import->error_message,
            validation_errors: $import->validation_errors,
            started_at: $import->started_at?->toDateTimeString(),
            completed_at: $import->completed_at?->toDateTimeString(),
            created_at: $import->created_at->toDateTimeString(),
            updated_at: $import->updated_at->toDateTimeString(),
        );
    }

    public function isProcessing(): bool
    {
        return $this->status === ImportStatus::PROCESSING;
    }

    public function isPending(): bool
    {
        return $this->status === ImportStatus::PENDING;
    }

    public function isCompleted(): bool
    {
        return $this->status === ImportStatus::COMPLETED;
    }

    public function isFailed(): bool
    {
        return $this->status === ImportStatus::FAILED;
    }

    public function getProgressPercentage(): int
    {
        if ($this->total_rows === 0) {
            return 0;
        }

        $processed = $this->imported_cards_count + $this->updated_cards_count + $this->skipped_rows;

        return min(100, (int) (($processed / $this->total_rows) * 100));
    }
}
