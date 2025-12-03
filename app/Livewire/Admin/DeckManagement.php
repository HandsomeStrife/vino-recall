<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use Domain\Admin\Repositories\AdminRepository;
use Domain\Deck\Actions\CreateDeckAction;
use Domain\Deck\Actions\DeleteDeckAction;
use Domain\Deck\Actions\UpdateDeckAction;
use Domain\Deck\Repositories\DeckRepository;
use Livewire\Component;

class DeckManagement extends Component
{
    public string $name = '';

    public ?string $description = null;

    public bool $is_active = true;

    public ?int $editingDeckId = null;

    public function render(DeckRepository $deckRepository)
    {
        $decks = $deckRepository->getAll();

        return view('livewire.admin.deck-management', [
            'decks' => $decks,
        ]);
    }

    public function createDeck(CreateDeckAction $createDeckAction, AdminRepository $adminRepository): void
    {
        $this->validate([
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $admin = $adminRepository->getLoggedInAdmin();
        $createDeckAction->execute(
            name: $this->name,
            description: $this->description,
            is_active: $this->is_active,
            created_by: $admin->id
        );

        $this->reset(['name', 'description', 'is_active']);
        session()->flash('message', 'Deck created successfully.');
    }

    public function editDeck(int $deckId, DeckRepository $deckRepository): void
    {
        $deck = $deckRepository->findById($deckId);
        if ($deck) {
            $this->editingDeckId = $deckId;
            $this->name = $deck->name;
            $this->description = $deck->description;
            $this->is_active = $deck->is_active;
        }
    }

    public function updateDeck(UpdateDeckAction $updateDeckAction): void
    {
        if ($this->editingDeckId) {
            $this->validate([
                'name' => ['required', 'string', 'min:3', 'max:255'],
                'description' => ['nullable', 'string', 'max:1000'],
            ]);

            $updateDeckAction->execute(
                deckId: $this->editingDeckId,
                name: $this->name,
                description: $this->description,
                is_active: $this->is_active
            );
            $this->reset(['name', 'description', 'is_active', 'editingDeckId']);
            session()->flash('message', 'Deck updated successfully.');
        }
    }

    public function deleteDeck(int $deckId, DeleteDeckAction $deleteDeckAction): void
    {
        $deleteDeckAction->execute($deckId);
        session()->flash('message', 'Deck deleted successfully.');
    }
}
