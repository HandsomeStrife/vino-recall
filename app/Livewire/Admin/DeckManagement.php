<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use Domain\Admin\Repositories\AdminRepository;
use Domain\Category\Repositories\CategoryRepository;
use Domain\Deck\Actions\CreateDeckAction;
use Domain\Deck\Actions\DeleteDeckAction;
use Domain\Deck\Actions\UpdateDeckAction;
use Domain\Deck\Exceptions\DeckHierarchyException;
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

    public string $deck_type = 'standalone';

    public ?int $parent_deck_id = null;

    public function render(DeckRepository $deckRepository, CategoryRepository $categoryRepository)
    {
        $decks = $deckRepository->getAll();
        $categories = $categoryRepository->getAll();
        $availableParents = $deckRepository->getAvailableParents($this->editingDeckId);

        return view('livewire.admin.deck-management', [
            'decks' => $decks,
            'categories' => $categories,
            'availableParents' => $availableParents,
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
            $this->deck_type = $deck->deck_type;
            $this->parent_deck_id = $deck->parent_deck_id;
            $this->image = null;
            $this->showModal = true;
        }
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function updatedDeckType(): void
    {
        // Clear parent when switching away from child type
        if ($this->deck_type !== 'child') {
            $this->parent_deck_id = null;
        }
    }

    public function createDeck(CreateDeckAction $createDeckAction, AdminRepository $adminRepository): void
    {
        $this->validate([
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'image' => ['nullable', 'image', 'max:5120'],
            'selectedCategories' => ['nullable', 'array'],
            'selectedCategories.*' => ['exists:categories,id'],
            'deck_type' => ['required', 'in:standalone,collection,child'],
            'parent_deck_id' => ['nullable', 'required_if:deck_type,child', 'exists:decks,id'],
        ]);

        $imagePath = null;
        if ($this->image) {
            $imagePath = $this->image->store('decks', 'public');
        }

        try {
            $admin = $adminRepository->getLoggedInAdmin();
            $createDeckAction->execute(
                name: $this->name,
                description: $this->description,
                is_active: $this->is_active,
                created_by: $admin->id,
                image_path: $imagePath,
                categoryIds: $this->selectedCategories,
                parent_deck_id: $this->deck_type === 'child' ? $this->parent_deck_id : null,
                is_collection: $this->deck_type === 'collection'
            );

            $this->closeModal();
            session()->flash('message', 'Deck created successfully.');
        } catch (DeckHierarchyException $e) {
            $this->addError('deck_type', $e->getMessage());
        }
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
                'deck_type' => ['required', 'in:standalone,collection,child'],
                'parent_deck_id' => ['nullable', 'required_if:deck_type,child', 'exists:decks,id'],
            ]);

            $imagePath = $this->existingImagePath;
            if ($this->image) {
                $imagePath = $this->image->store('decks', 'public');
            }

            try {
                $updateDeckAction->execute(
                    deckId: $this->editingDeckId,
                    name: $this->name,
                    description: $this->description,
                    is_active: $this->is_active,
                    image_path: $imagePath,
                    categoryIds: $this->selectedCategories,
                    parent_deck_id: $this->deck_type === 'child' ? $this->parent_deck_id : null,
                    clear_parent: $this->deck_type !== 'child',
                    is_collection: $this->deck_type === 'collection'
                );

                $this->closeModal();
                session()->flash('message', 'Deck updated successfully.');
            } catch (DeckHierarchyException $e) {
                $this->addError('deck_type', $e->getMessage());
            }
        }
    }

    public function deleteDeck(int $deckId, DeleteDeckAction $deleteDeckAction): void
    {
        $deleteDeckAction->execute($deckId);
        session()->flash('message', 'Deck deleted successfully.');
    }

    private function resetForm(): void
    {
        $this->reset(['name', 'description', 'is_active', 'editingDeckId', 'image', 'existingImagePath', 'selectedCategories', 'deck_type', 'parent_deck_id']);
        $this->is_active = true;
        $this->deck_type = 'standalone';
    }
}
