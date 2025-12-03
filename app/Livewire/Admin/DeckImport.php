<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use Domain\Admin\Repositories\AdminRepository;
use Domain\Deck\Actions\ImportDeckAction;
use Domain\Deck\Enums\ImportFormat;
use Domain\Deck\Models\DeckImport;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

class DeckImport extends Component
{
    use WithFileUploads;

    public $file;

    public string $deckName = '';

    public string $description = '';

    public string $format = 'csv';

    public function render(AdminRepository $adminRepository)
    {
        $admin = $adminRepository->getLoggedInAdmin();

        $imports = DeckImport::where('user_id', $admin->id)
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get();

        return view('livewire.admin.deck-import', [
            'imports' => $imports,
        ]);
    }

    public function import(ImportDeckAction $importDeckAction, AdminRepository $adminRepository): void
    {
        // Apply rate limiting
        $executed = RateLimiter::attempt(
            'import-deck:' . auth()->guard('admin')->id(),
            5, // Max 5 attempts
            function () {
                // This callback is executed if the rate limit is not exceeded
            },
            60 * 60 // Per hour
        );

        if (! $executed) {
            session()->flash('error', __('admin.import_rate_limit_exceeded'));
            return;
        }

        $this->validate([
            'file' => ['required', 'file', 'mimes:csv,txt,apkg', 'max:10240'], // Max 10MB
            'deckName' => ['required', 'string', 'min:3', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'format' => ['required', 'in:csv,apkg'],
        ]);

        $admin = $adminRepository->getLoggedInAdmin();

        // Generate a cryptographically secure temporary file name
        $originalExtension = $this->file->getClientOriginalExtension();
        $tempFileName = Str::random(40) . '.' . $originalExtension;
        $filePath = $this->file->storeAs('imports', $tempFileName, 'local');
        $fullPath = storage_path('app/' . $filePath);

        try {
            $importFormat = $this->format === 'apkg' ? ImportFormat::APKG : ImportFormat::CSV;

            // Execute import synchronously for now (could be queued)
            $importDeckAction->execute(
                userId: $admin->id,
                filePath: $fullPath,
                deckName: $this->deckName,
                description: $this->description,
                format: $importFormat
            );

            session()->flash('message', __('admin.deck_imported_successfully'));
            $this->reset(['file', 'deckName', 'description', 'format']);
        } catch (\Exception $e) {
            // Log the full error for debugging, but show a generic message to the user
            \Log::error("Deck import failed for admin {$admin->id}: " . $e->getMessage(), ['exception' => $e]);
            session()->flash('error', __('admin.import_failed') . ': ' . __('admin.generic_error_message'));
        } finally {
            // Clean up temp file
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
        }
    }

    public function downloadTemplate(): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        return response()->download(
            public_path('templates/deck-import-template.csv'),
            'deck-import-template.csv'
        );
    }
}

            public_path('templates/deck-import-template.csv'),
            'deck-import-template.csv'
        );
    }
}
