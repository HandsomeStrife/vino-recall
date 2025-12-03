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
        CardType $cardType = CardType::TRADITIONAL,
        ?array $answerChoices = null,
        ?int $correctAnswerIndex = null
    ): CardData {
        // Validate multiple choice fields if card type is multiple choice
        if ($cardType === CardType::MULTIPLE_CHOICE) {
            if ($answerChoices === null || count($answerChoices) < 2) {
                throw new \InvalidArgumentException('Multiple choice cards must have at least 2 answer choices.');
            }

            if ($correctAnswerIndex === null || $correctAnswerIndex < 0 || $correctAnswerIndex >= count($answerChoices)) {
                throw new \InvalidArgumentException('Multiple choice cards must have a valid correct answer index.');
            }
        }

        $card = Card::create([
            'deck_id' => $deckId,
            'card_type' => $cardType->value,
            'question' => $question,
            'answer' => $answer,
            'image_path' => $image_path,
            'answer_choices' => $answerChoices ? json_encode($answerChoices) : null,
            'correct_answer_index' => $correctAnswerIndex,
        ]);

        return CardData::fromModel($card);
    }
}
