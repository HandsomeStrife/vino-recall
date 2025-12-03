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
        public ImportFormat $format,
        public ImportStatus $status,
        public int $imported_cards_count,
        public ?string $error_message,
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
            format: $import->format,
            status: $import->status,
            imported_cards_count: $import->imported_cards_count,
            error_message: $import->error_message,
            created_at: $import->created_at->toDateTimeString(),
            updated_at: $import->updated_at->toDateTimeString(),
        );
    }
}

