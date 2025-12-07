<?php

declare(strict_types=1);

namespace App\Livewire\Forms\Admin;

use Livewire\Attributes\Validate;
use Livewire\Form;

class CardForm extends Form
{
    #[Validate('required|string|min:3')]
    public string $question = '';

    public string $answer = '';

    public array $answer_choices = ['', '', '', ''];

    public array $correct_answer_indices = [0];

    public bool $is_multi_select = false;

    public $image = null;

    public ?string $existingImagePath = null;

    public ?int $editingCardId = null;

    /**
     * Get the trimmed question text
     */
    public function getTrimmedQuestion(): string
    {
        return trim($this->question);
    }

    /**
     * Get filtered (non-empty) and trimmed answer choices
     */
    public function getFilledChoices(): array
    {
        $trimmed = array_map(fn ($choice) => trim($choice), $this->answer_choices);
        return array_values(array_filter($trimmed, fn ($choice) => !empty($choice)));
    }

    /**
     * Validate answer choices and get valid indices
     */
    public function validateChoicesAndGetIndices(): ?array
    {
        $filledChoices = $this->getFilledChoices();

        // Validate at least 2 non-empty answer choices
        if (count($filledChoices) < 2) {
            $this->addError('form.answer_choices', 'Please provide at least 2 answer choices.');
            return null;
        }

        // Validate correct answer indices - compare trimmed values
        $validIndices = [];
        foreach ($this->correct_answer_indices as $index) {
            if (isset($this->answer_choices[$index])) {
                $trimmedChoice = trim($this->answer_choices[$index]);
                if (!empty($trimmedChoice)) {
                    $foundIndex = array_search($trimmedChoice, $filledChoices, true);
                    if ($foundIndex !== false) {
                        $validIndices[] = $foundIndex;
                    }
                }
            }
        }

        if (empty($validIndices)) {
            $this->addError('form.correct_answer_indices', 'Please mark at least one correct answer.');
            return null;
        }

        return $validIndices;
    }

    /**
     * Build the answer string from correct choices
     */
    public function buildAnswerString(array $filledChoices, array $validIndices): string
    {
        $correctAnswers = [];
        foreach ($validIndices as $idx) {
            $correctAnswers[] = $filledChoices[$idx];
        }
        return implode(', ', $correctAnswers);
    }

    /**
     * Reset form to default values
     */
    public function resetForm(): void
    {
        $this->question = '';
        $this->answer = '';
        $this->answer_choices = ['', '', '', ''];
        $this->correct_answer_indices = [0];
        $this->is_multi_select = false;
        $this->image = null;
        $this->existingImagePath = null;
        $this->editingCardId = null;
    }

    /**
     * Fill form with card data for editing
     */
    public function setCardData(
        int $cardId,
        string $question,
        string $answer,
        ?array $answerChoices,
        ?array $correctAnswerIndices,
        bool $isMultiSelect,
        ?string $imagePath
    ): void {
        $this->editingCardId = $cardId;
        $this->question = $question;
        $this->answer = $answer;
        $this->answer_choices = $answerChoices ?? ['', '', '', ''];
        $this->correct_answer_indices = $correctAnswerIndices ?? [0];
        $this->is_multi_select = $isMultiSelect;
        $this->existingImagePath = $imagePath;
        $this->image = null;
    }
}
