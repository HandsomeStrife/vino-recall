<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use Domain\Card\Actions\CreateCardAction;
use Domain\Card\Actions\DeleteCardAction;
use Domain\Card\Actions\UpdateCardAction;
use Domain\Card\Enums\CardType;
use Domain\Card\Repositories\CardRepository;
use Domain\Deck\Repositories\DeckRepository;
use Livewire\Component;
use Livewire\WithFileUploads;

class CardManagement extends Component
{
    use WithFileUploads;

    public ?int $deck_id = null;

    public string $question = '';

    public string $answer = '';

    public $image = null;

    public ?int $editingCardId = null;

    public array $answer_choices = ['', '', '', ''];

    /** @var array<int> */
    public array $correct_answer_indices = [0];

    public function updatedCorrectAnswerIndices(): void
    {
        $this->updateAnswerFromCorrectChoices();
    }

    public function updatedAnswerChoices(): void
    {
        $this->updateAnswerFromCorrectChoices();
    }

    private function updateAnswerFromCorrectChoices(): void
    {
        if (count($this->correct_answer_indices) > 0) {
            $correctAnswers = [];
            foreach ($this->correct_answer_indices as $index) {
                if (isset($this->answer_choices[$index]) && $this->answer_choices[$index] !== '') {
                    $correctAnswers[] = $this->answer_choices[$index];
                }
            }
            $this->answer = implode(', ', $correctAnswers);
        }
    }

    /**
     * Toggle a correct answer index for multiple correct answers.
     */
    public function toggleCorrectAnswer(int $index): void
    {
        if (in_array($index, $this->correct_answer_indices, true)) {
            // Don't allow removing the last correct answer
            if (count($this->correct_answer_indices) > 1) {
                $this->correct_answer_indices = array_values(array_filter(
                    $this->correct_answer_indices,
                    fn ($i) => $i !== $index
                ));
            }
        } else {
            $this->correct_answer_indices[] = $index;
            sort($this->correct_answer_indices);
        }
        $this->updateAnswerFromCorrectChoices();
    }

    public function render(CardRepository $cardRepository, DeckRepository $deckRepository)
    {
        $cards = $cardRepository->getAll();
        $decks = $deckRepository->getAll();

        return view('livewire.admin.card-management', [
            'cards' => $cards,
            'decks' => $decks,
        ]);
    }

    public function createCard(CreateCardAction $createCardAction): void
    {
        $this->validate([
            'deck_id' => ['required', 'integer', 'exists:decks,id'],
            'question' => ['required', 'string', 'min:3'],
            'answer' => ['required', 'string', 'min:1'],
            'image' => ['nullable', 'image', 'max:5120'],
            'answer_choices' => ['required', 'array', 'min:2'],
            'answer_choices.*' => ['required', 'string'],
            'correct_answer_indices' => ['required', 'array', 'min:1'],
            'correct_answer_indices.*' => ['required', 'integer', 'min:0'],
        ]);

        $imagePath = null;
        if ($this->image) {
            $imagePath = $this->image->store('cards', 'public');
        }

        $answerChoices = array_values(array_filter($this->answer_choices, fn ($choice) => ! empty($choice)));

        $createCardAction->execute(
            deckId: $this->deck_id,
            question: $this->question,
            answer: $this->answer,
            image_path: $imagePath,
            cardType: CardType::MULTIPLE_CHOICE,
            answerChoices: $answerChoices,
            correctAnswerIndices: $this->correct_answer_indices
        );

        $this->reset(['deck_id', 'question', 'answer', 'image', 'editingCardId']);
        $this->answer_choices = ['', '', '', ''];
        $this->correct_answer_indices = [0];
        session()->flash('message', 'Card created successfully.');
    }

    public function editCard(int $cardId, CardRepository $cardRepository): void
    {
        $card = $cardRepository->findById($cardId);
        if ($card) {
            $this->editingCardId = $cardId;
            $this->deck_id = $card->deck_id;
            $this->question = $card->question;
            $this->answer = $card->answer;
            $this->answer_choices = $card->answer_choices ?? ['', '', '', ''];
            $this->correct_answer_indices = $card->correct_answer_indices ?? [0];
        }
    }

    public function updateCard(UpdateCardAction $updateCardAction): void
    {
        if ($this->editingCardId) {
            $this->validate([
                'deck_id' => ['required', 'integer', 'exists:decks,id'],
                'question' => ['required', 'string', 'min:3'],
                'answer' => ['required', 'string', 'min:1'],
                'image' => ['nullable', 'image', 'max:5120'],
                'answer_choices' => ['required', 'array', 'min:2'],
                'answer_choices.*' => ['required', 'string'],
                'correct_answer_indices' => ['required', 'array', 'min:1'],
                'correct_answer_indices.*' => ['required', 'integer', 'min:0'],
            ]);

            $imagePath = null;
            if ($this->image) {
                $imagePath = $this->image->store('cards', 'public');
            }

            $answerChoices = array_values(array_filter($this->answer_choices, fn ($choice) => ! empty($choice)));

            $updateCardAction->execute(
                cardId: $this->editingCardId,
                deckId: $this->deck_id,
                question: $this->question,
                answer: $this->answer,
                image_path: $imagePath,
                cardType: CardType::MULTIPLE_CHOICE,
                answerChoices: $answerChoices,
                correctAnswerIndices: $this->correct_answer_indices
            );

            $this->reset(['deck_id', 'question', 'answer', 'image', 'editingCardId']);
            $this->answer_choices = ['', '', '', ''];
            $this->correct_answer_indices = [0];
            session()->flash('message', 'Card updated successfully.');
        }
    }

    public function deleteCard(int $cardId, DeleteCardAction $deleteCardAction): void
    {
        $deleteCardAction->execute($cardId);
        session()->flash('message', 'Card deleted successfully.');
    }
}
