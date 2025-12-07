<?php

declare(strict_types=1);

namespace Domain\Card\Data;

use Domain\Card\Enums\CardType;
use Domain\Card\Models\Card;
use Spatie\LaravelData\Data;

class CardData extends Data
{
    public function __construct(
        public int $id,
        public string $shortcode,
        public int $deck_id,
        public CardType $card_type,
        public string $question,
        public string $answer,
        public ?string $image_path,
        public ?array $answer_choices,
        public ?array $correct_answer_indices,
        public bool $is_multi_select,
        public string $created_at,
        public string $updated_at,
    ) {}

    public static function fromModel(Card $card): self
    {
        return new self(
            id: $card->id,
            shortcode: $card->shortcode ?? '',
            deck_id: $card->deck_id,
            card_type: is_string($card->card_type) ? CardType::from($card->card_type) : $card->card_type,
            question: $card->question,
            answer: $card->answer,
            image_path: $card->image_path,
            answer_choices: $card->answer_choices ? json_decode($card->answer_choices, true) : null,
            correct_answer_indices: $card->correct_answer_indices ? json_decode($card->correct_answer_indices, true) : null,
            is_multi_select: (bool) $card->is_multi_select,
            created_at: $card->created_at->toDateTimeString(),
            updated_at: $card->updated_at->toDateTimeString(),
        );
    }

    /**
     * Check if this card should show "Select all that apply".
     * Returns true if is_multi_select is enabled OR if there are multiple correct answers.
     */
    public function hasMultipleCorrectAnswers(): bool
    {
        return $this->is_multi_select || (is_array($this->correct_answer_indices) && count($this->correct_answer_indices) > 1);
    }
}
