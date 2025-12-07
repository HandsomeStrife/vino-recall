<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Livewire\Forms\Admin\CardForm;
use Domain\Card\Actions\CreateCardAction;
use Domain\Card\Actions\DeleteCardAction;
use Domain\Card\Actions\ExportDeckCardsAction;
use Domain\Card\Actions\ImportDeckCardsAction;
use Domain\Card\Actions\UpdateCardAction;
use Domain\Card\Enums\CardType;
use Domain\Card\Repositories\CardRepository;
use Domain\Deck\Repositories\DeckRepository;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DeckCards extends Component
{
    use WithFileUploads;

    #[Locked]
    public int $deckId;

    public CardForm $form;

    public bool $showEditModal = false;

    public bool $showAddForm = false;

    public bool $showImportModal = false;

    #[Validate('required|file|mimes:csv,txt|max:1024')]
    public $importFile = null;

    public function mount(int $deckId): void
    {
        $this->deckId = $deckId;
    }

    public function updatedFormCorrectAnswerIndices(): void
    {
        $this->updateAnswerFromCorrectChoices();
    }

    public function updatedFormAnswerChoices(): void
    {
        $this->updateAnswerFromCorrectChoices();
    }

    private function updateAnswerFromCorrectChoices(): void
    {
        if (count($this->form->correct_answer_indices) > 0) {
            $correctAnswers = [];
            foreach ($this->form->correct_answer_indices as $index) {
                if (isset($this->form->answer_choices[$index]) && $this->form->answer_choices[$index] !== '') {
                    $correctAnswers[] = $this->form->answer_choices[$index];
                }
            }
            $this->form->answer = implode(', ', $correctAnswers);
        }
    }

    public function toggleCorrectAnswer(int $index): void
    {
        if (in_array($index, $this->form->correct_answer_indices, true)) {
            if (count($this->form->correct_answer_indices) > 1) {
                $this->form->correct_answer_indices = array_values(array_filter(
                    $this->form->correct_answer_indices,
                    fn ($i) => $i !== $index
                ));
            }
        } else {
            $this->form->correct_answer_indices[] = $index;
            sort($this->form->correct_answer_indices);
        }

        // Auto-enable multi-select when multiple answers are marked correct
        if (count($this->form->correct_answer_indices) > 1) {
            $this->form->is_multi_select = true;
        }

        $this->updateAnswerFromCorrectChoices();
    }

    public function render(CardRepository $cardRepository, DeckRepository $deckRepository)
    {
        $deck = $deckRepository->findById($this->deckId);
        $cards = $cardRepository->getByDeckId($this->deckId);

        return view('livewire.admin.deck-cards', [
            'deck' => $deck,
            'cards' => $cards,
        ]);
    }

    public function toggleAddForm(): void
    {
        $this->showAddForm = !$this->showAddForm;
        if ($this->showAddForm) {
            $this->form->resetForm();
        }
    }

    public function createCard(CreateCardAction $createCardAction): void
    {
        // Validate answer choices
        $validIndices = $this->form->validateChoicesAndGetIndices();
        if ($validIndices === null) {
            return;
        }

        $this->form->validate();

        $filledChoices = $this->form->getFilledChoices();

        $imagePath = null;
        if ($this->form->image) {
            $imagePath = $this->form->image->store('cards', 'public');
        }

        $answerString = $this->form->buildAnswerString($filledChoices, $validIndices);

        $createCardAction->execute(
            deckId: $this->deckId,
            question: $this->form->getTrimmedQuestion(),
            answer: $answerString,
            image_path: $imagePath,
            cardType: CardType::MULTIPLE_CHOICE,
            answerChoices: $filledChoices,
            correctAnswerIndices: $validIndices,
            isMultiSelect: $this->form->is_multi_select
        );

        // Reset form to initial values
        $this->form->resetForm();

        $this->dispatch('card-saved', message: 'Card created successfully!');
    }

    public function openEditModal(int $cardId, CardRepository $cardRepository): void
    {
        $card = $cardRepository->findById($cardId);
        if ($card) {
            $this->form->setCardData(
                cardId: $cardId,
                question: $card->question,
                answer: $card->answer,
                answerChoices: $card->answer_choices,
                correctAnswerIndices: $card->correct_answer_indices,
                isMultiSelect: $card->is_multi_select,
                imagePath: $card->image_path
            );
            $this->showEditModal = true;
        }
    }

    public function closeEditModal(): void
    {
        $this->showEditModal = false;
        $this->form->resetForm();
    }

    public function updateCard(UpdateCardAction $updateCardAction): void
    {
        if ($this->form->editingCardId) {
            // Validate answer choices
            $validIndices = $this->form->validateChoicesAndGetIndices();
            if ($validIndices === null) {
                return;
            }

            $this->form->validate();

            $filledChoices = $this->form->getFilledChoices();

            $imagePath = $this->form->existingImagePath;
            if ($this->form->image) {
                $imagePath = $this->form->image->store('cards', 'public');
            }

            $answerString = $this->form->buildAnswerString($filledChoices, $validIndices);

            $updateCardAction->execute(
                cardId: $this->form->editingCardId,
                deckId: $this->deckId,
                question: $this->form->getTrimmedQuestion(),
                answer: $answerString,
                image_path: $imagePath,
                cardType: CardType::MULTIPLE_CHOICE,
                answerChoices: $filledChoices,
                correctAnswerIndices: $validIndices,
                isMultiSelect: $this->form->is_multi_select
            );

            $this->closeEditModal();
            $this->dispatch('card-saved', message: 'Card updated successfully!');
        }
    }

    public function deleteCard(int $cardId, DeleteCardAction $deleteCardAction): void
    {
        $deleteCardAction->execute($cardId);
        $this->dispatch('card-saved', message: 'Card deleted successfully!');
    }

    public function exportCards(ExportDeckCardsAction $exportAction, DeckRepository $deckRepository): StreamedResponse
    {
        $deck = $deckRepository->findById($this->deckId);
        $deckName = $deck ? preg_replace('/[^a-zA-Z0-9_-]/', '_', $deck->name) : 'deck';
        $filename = "{$deckName}_cards_export.csv";

        $csvContent = $exportAction->execute($this->deckId);

        return response()->streamDownload(function () use ($csvContent) {
            echo $csvContent;
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function openImportModal(): void
    {
        $this->showImportModal = true;
        $this->importFile = null;
    }

    public function closeImportModal(): void
    {
        $this->showImportModal = false;
        $this->importFile = null;
        $this->resetValidation('importFile');
    }

    public function importCards(ImportDeckCardsAction $importAction): void
    {
        $this->validate([
            'importFile' => 'required|file|mimes:csv,txt|max:1024',
        ]);

        $csvContent = file_get_contents($this->importFile->getRealPath());

        try {
            $result = $importAction->execute($this->deckId, $csvContent);

            $this->closeImportModal();

            if ($result->skipped > 0) {
                $errorSummary = implode('; ', array_slice($result->errors, 0, 3));
                if (count($result->errors) > 3) {
                    $errorSummary .= '...';
                }
                $this->dispatch('card-saved', message: "Imported {$result->imported} cards, skipped {$result->skipped}. Errors: {$errorSummary}");
            } else {
                $this->dispatch('card-saved', message: "Successfully imported {$result->imported} cards!");
            }
        } catch (\InvalidArgumentException $e) {
            $this->addError('importFile', $e->getMessage());
        }
    }
}
