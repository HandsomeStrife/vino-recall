<?php

declare(strict_types=1);

namespace Domain\Card\Actions;

use Domain\Card\Data\CardData;
use Domain\Card\Enums\CardType;
use Domain\Card\Models\Card;

class CreateCardAction
{
    public function execute(
        int $deckId,
        string $question,
        string $answer,
        ?string $image_path = null,
        CardType $cardType = CardType::MULTIPLE_CHOICE,
        ?array $answerChoices = null,
        ?array $correctAnswerIndices = null
    ): CardData {
        // Validate multiple choice fields - all cards must have valid MC data
        if ($answerChoices === null || count($answerChoices) < 2) {
            throw new \InvalidArgumentException('Cards must have at least 2 answer choices.');
        }

        if ($correctAnswerIndices === null || count($correctAnswerIndices) < 1) {
            throw new \InvalidArgumentException('Cards must have at least one correct answer index.');
        }

        // Validate all indices are within bounds
        foreach ($correctAnswerIndices as $index) {
            if ($index < 0 || $index >= count($answerChoices)) {
                throw new \InvalidArgumentException('Cards must have valid correct answer indices.');
            }
        }

        $card = Card::create([
            'deck_id' => $deckId,
            'card_type' => CardType::MULTIPLE_CHOICE->value,
            'question' => $question,
            'answer' => $answer,
            'image_path' => $image_path,
            'answer_choices' => json_encode($answerChoices),
            'correct_answer_indices' => json_encode($correctAnswerIndices),
        ]);

        return CardData::fromModel($card);
    }
}
