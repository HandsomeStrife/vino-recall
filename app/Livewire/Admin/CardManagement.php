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

    public string $card_type = 'multiple_choice';

    public array $answer_choices = ['', '', '', ''];

    public ?int $correct_answer_index = 0;

    public function updatedCorrectAnswerIndex(): void
    {
        // Auto-update answer field when correct answer index changes for multiple choice
        if ($this->card_type === 'multiple_choice' && isset($this->answer_choices[$this->correct_answer_index])) {
            $this->answer = $this->answer_choices[$this->correct_answer_index];
        }
    }

    public function updatedAnswerChoices(): void
    {
        // Auto-update answer field when answer choices change for multiple choice
        if ($this->card_type === 'multiple_choice' && isset($this->answer_choices[$this->correct_answer_index])) {
            $this->answer = $this->answer_choices[$this->correct_answer_index];
        }
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
        $rules = [
            'deck_id' => ['required', 'integer', 'exists:decks,id'],
            'question' => ['required', 'string', 'min:3'],
            'answer' => ['required', 'string', 'min:3'],
            'image' => ['nullable', 'image', 'max:5120'],
            'card_type' => ['required', 'in:traditional,multiple_choice'],
        ];

        if ($this->card_type === 'multiple_choice') {
            $rules['answer_choices'] = ['required', 'array', 'min:2'];
            $rules['answer_choices.*'] = ['required', 'string'];
            $rules['correct_answer_index'] = ['required', 'integer', 'min:0'];
        }

        $this->validate($rules);

        $imagePath = null;
        if ($this->image) {
            $imagePath = $this->image->store('cards', 'public');
        }

        $answerChoices = null;
        $correctAnswerIndex = null;

        if ($this->card_type === 'multiple_choice') {
            $answerChoices = array_values(array_filter($this->answer_choices, fn ($choice) => ! empty($choice)));
            $correctAnswerIndex = $this->correct_answer_index;
        }

        $createCardAction->execute(
            deckId: $this->deck_id,
            question: $this->question,
            answer: $this->answer,
            image_path: $imagePath,
            cardType: CardType::from($this->card_type),
            answerChoices: $answerChoices,
            correctAnswerIndex: $correctAnswerIndex
        );

        $this->reset(['deck_id', 'question', 'answer', 'image', 'editingCardId']);
        $this->card_type = 'multiple_choice';
        $this->answer_choices = ['', '', '', ''];
        $this->correct_answer_index = 0;
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
            $this->card_type = $card->card_type->value;
            $this->answer_choices = $card->answer_choices ?? ['', '', '', ''];
            $this->correct_answer_index = $card->correct_answer_index;
        }
    }

    public function updateCard(UpdateCardAction $updateCardAction): void
    {
        if ($this->editingCardId) {
            $rules = [
                'deck_id' => ['required', 'integer', 'exists:decks,id'],
                'question' => ['required', 'string', 'min:3'],
                'answer' => ['required', 'string', 'min:3'],
                'image' => ['nullable', 'image', 'max:5120'],
                'card_type' => ['required', 'in:traditional,multiple_choice'],
            ];

            if ($this->card_type === 'multiple_choice') {
                $rules['answer_choices'] = ['required', 'array', 'min:2'];
                $rules['answer_choices.*'] = ['required', 'string'];
                $rules['correct_answer_index'] = ['required', 'integer', 'min:0'];
            }

            $this->validate($rules);

            $imagePath = null;
            if ($this->image) {
                $imagePath = $this->image->store('cards', 'public');
            }

            $answerChoices = null;
            $correctAnswerIndex = null;

            if ($this->card_type === 'multiple_choice') {
                $answerChoices = array_values(array_filter($this->answer_choices, fn ($choice) => ! empty($choice)));
                $correctAnswerIndex = $this->correct_answer_index;
            }

            $updateCardAction->execute(
                cardId: $this->editingCardId,
                deckId: $this->deck_id,
                question: $this->question,
                answer: $this->answer,
                image_path: $imagePath,
                cardType: CardType::from($this->card_type),
                answerChoices: $answerChoices,
                correctAnswerIndex: $correctAnswerIndex
            );

            $this->reset(['deck_id', 'question', 'answer', 'image', 'editingCardId']);
            $this->card_type = 'multiple_choice';
            $this->answer_choices = ['', '', '', ''];
            $this->correct_answer_index = 0;
            session()->flash('message', 'Card updated successfully.');
        }
    }

    public function deleteCard(int $cardId, DeleteCardAction $deleteCardAction): void
    {
        $deleteCardAction->execute($cardId);
        session()->flash('message', 'Card deleted successfully.');
    }
}
