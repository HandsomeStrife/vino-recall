<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use Domain\Admin\Repositories\AdminRepository;
use Domain\Category\Repositories\CategoryRepository;
use Domain\Deck\Actions\CreateDeckAction;
use Domain\Deck\Actions\DeleteDeckAction;
use Domain\Deck\Actions\UpdateDeckAction;
use Domain\Deck\Repositories\DeckRepository;
use Livewire\Component;
use Livewire\WithFileUploads;

class DeckManagement extends Component
{
    use WithFileUploads;

    public string $name = '';

    public ?string $description = null;

    public bool $is_active = true;

    public ?int $editingDeckId = null;

    public bool $showModal = false;

    public $image = null;

    public ?string $existingImagePath = null;

    public array $selectedCategories = [];

    public function render(DeckRepository $deckRepository, CategoryRepository $categoryRepository)
    {
        $decks = $deckRepository->getAll();
        $categories = $categoryRepository->getAll();

        return view('livewire.admin.deck-management', [
            'decks' => $decks,
            'categories' => $categories,
        ]);
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEditModal(int $deckId, DeckRepository $deckRepository): void
    {
        $deck = $deckRepository->findById($deckId);
        if ($deck) {
            $this->editingDeckId = $deckId;
            $this->name = $deck->name;
            $this->description = $deck->description;
            $this->is_active = $deck->is_active;
            $this->existingImagePath = $deck->image_path;
            $this->selectedCategories = $deck->category_ids ?? [];
            $this->image = null;
            $this->showModal = true;
        }
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function createDeck(CreateDeckAction $createDeckAction, AdminRepository $adminRepository): void
    {
        $this->validate([
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'image' => ['nullable', 'image', 'max:5120'],
            'selectedCategories' => ['nullable', 'array'],
            'selectedCategories.*' => ['exists:categories,id'],
        ]);

        $imagePath = null;
        if ($this->image) {
            $imagePath = $this->image->store('decks', 'public');
        }

        $admin = $adminRepository->getLoggedInAdmin();
        $createDeckAction->execute(
            name: $this->name,
            description: $this->description,
            is_active: $this->is_active,
            created_by: $admin->id,
            image_path: $imagePath,
            categoryIds: $this->selectedCategories
        );

        $this->closeModal();
        session()->flash('message', 'Deck created successfully.');
    }

    public function updateDeck(UpdateDeckAction $updateDeckAction): void
    {
        if ($this->editingDeckId) {
            $this->validate([
                'name' => ['required', 'string', 'min:3', 'max:255'],
                'description' => ['nullable', 'string', 'max:1000'],
                'image' => ['nullable', 'image', 'max:5120'],
                'selectedCategories' => ['nullable', 'array'],
                'selectedCategories.*' => ['exists:categories,id'],
            ]);

            $imagePath = $this->existingImagePath;
            if ($this->image) {
                $imagePath = $this->image->store('decks', 'public');
            }

            $updateDeckAction->execute(
                deckId: $this->editingDeckId,
                name: $this->name,
                description: $this->description,
                is_active: $this->is_active,
                image_path: $imagePath,
                categoryIds: $this->selectedCategories
            );

            $this->closeModal();
            session()->flash('message', 'Deck updated successfully.');
        }
    }

    public function deleteDeck(int $deckId, DeleteDeckAction $deleteDeckAction): void
    {
        $deleteDeckAction->execute($deckId);
        session()->flash('message', 'Deck deleted successfully.');
    }

    private function resetForm(): void
    {
        $this->reset(['name', 'description', 'is_active', 'editingDeckId', 'image', 'existingImagePath', 'selectedCategories']);
        $this->is_active = true;
    }
}
