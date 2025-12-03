<?php

declare(strict_types=1);

namespace Domain\Deck\Jobs;

use Domain\Deck\Actions\ImportDeckAction;
use Domain\Deck\Enums\ImportFormat;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessDeckImportJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $userId,
        public string $filePath,
        public string $deckName,
        public string $description,
        public ImportFormat $format
    ) {}

    /**
     * Execute the job.
     */
    public function handle(ImportDeckAction $importDeckAction): void
    {
        try {
            $importDeckAction->execute(
                userId: $this->userId,
                filePath: $this->filePath,
                deckName: $this->deckName,
                description: $this->description,
                format: $this->format
            );

            // Clean up uploaded file
            if (file_exists($this->filePath)) {
                unlink($this->filePath);
            }
        } catch (\Exception $e) {
            \Log::error("Deck import job failed: {$e->getMessage()}", [
                'user_id' => $this->userId,
                'file_path' => $this->filePath,
            ]);

            // Clean up uploaded file even on failure
            if (file_exists($this->filePath)) {
                unlink($this->filePath);
            }

            throw $e;
        }
    }
}

