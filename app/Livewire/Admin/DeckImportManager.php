<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use Domain\Admin\Repositories\AdminRepository;
use Domain\Deck\Actions\ImportDeckAction;
use Domain\Deck\Enums\ImportFormat;
use Domain\Deck\Models\Deck;
use Domain\Deck\Models\DeckImport;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

class DeckImportManager extends Component
{
    use WithFileUploads;

    public $file;

    public string $deckName = '';

    public string $description = '';

    public string $format = 'csv';

    public string $importMode = 'new'; // 'new' or 'existing'

    public ?int $selectedDeckId = null;

    public bool $showProgressModal = false;

    public ?int $currentImportId = null;

    public array $validationResult = [];

    public function render(AdminRepository $adminRepository)
    {
        $admin = $adminRepository->getLoggedInAdmin();

        $imports = DeckImport::where('user_id', $admin->id)
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get();

        $decks = Deck::orderBy('name')->get();

        return view('livewire.admin.deck-import', [
            'imports' => $imports,
            'decks' => $decks,
            'formatOptions' => [
                'csv' => ImportFormat::CSV->label(),
                'txt' => ImportFormat::TXT->label(),
            ],
        ]);
    }

    public function updatedFile(): void
    {
        // Clear previous validation when file changes
        $this->validationResult = [];
    }

    public function validateFile(ImportDeckAction $importDeckAction): void
    {
        $this->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:10240'],
            'format' => ['required', 'in:csv,txt'],
        ]);

        $tempPath = $this->file->getRealPath();
        $importFormat = $this->format === 'txt' ? ImportFormat::TXT : ImportFormat::CSV;

        $this->validationResult = $importDeckAction->validateFile($tempPath, $importFormat);
    }

    public function import(ImportDeckAction $importDeckAction, AdminRepository $adminRepository): void
    {
        // Apply rate limiting
        $executed = RateLimiter::attempt(
            'import-deck:' . auth()->guard('admin')->id(),
            5,
            function () {},
            60 * 60
        );

        if (! $executed) {
            session()->flash('error', __('admin.import_rate_limit_exceeded'));

            return;
        }

        // Validation rules based on import mode
        $rules = [
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:10240'],
            'format' => ['required', 'in:csv,txt'],
            'importMode' => ['required', 'in:new,existing'],
        ];

        if ($this->importMode === 'new') {
            $rules['deckName'] = ['required', 'string', 'min:3', 'max:255'];
            $rules['description'] = ['nullable', 'string', 'max:1000'];
        } else {
            $rules['selectedDeckId'] = ['required', 'integer', 'exists:decks,id'];
        }

        $this->validate($rules);

        $admin = $adminRepository->getLoggedInAdmin();

        try {
            $tempPath = $this->file->getRealPath();
            $originalFilename = $this->file->getClientOriginalName();
            $importFormat = $this->format === 'txt' ? ImportFormat::TXT : ImportFormat::CSV;

            // Execute import (will dispatch job)
            $importData = $importDeckAction->execute(
                userId: $admin->id,
                filePath: $tempPath,
                originalFilename: $originalFilename,
                format: $importFormat,
                deckId: $this->importMode === 'existing' ? $this->selectedDeckId : null,
                deckName: $this->importMode === 'new' ? $this->deckName : null,
                description: $this->description
            );

            // Show progress modal
            $this->currentImportId = $importData->id;
            $this->showProgressModal = true;

            // Reset form
            $this->reset(['file', 'deckName', 'description', 'validationResult']);
        } catch (\Exception $e) {
            \Log::error("Deck import failed for admin {$admin->id}: " . $e->getMessage(), ['exception' => $e]);
            session()->flash('error', $e->getMessage());
        }
    }

    #[Computed]
    public function currentImport(): ?DeckImport
    {
        if (! $this->currentImportId) {
            return null;
        }

        return DeckImport::find($this->currentImportId);
    }

    public function refreshImportStatus(): void
    {
        // This method is called by polling to refresh the import status
        // The currentImport computed property will automatically fetch fresh data
        unset($this->currentImport);
    }

    public function closeProgressModal(): void
    {
        $this->showProgressModal = false;
        $this->currentImportId = null;
        $this->validationResult = [];
    }

    public function downloadTemplate(): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        return response()->download(
            public_path('templates/deck-import-template.csv'),
            'deck-import-template.csv'
        );
    }

    public function downloadTxtTemplate(): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        return response()->download(
            public_path('templates/deck-import-template.txt'),
            'deck-import-template.txt'
        );
    }
}
