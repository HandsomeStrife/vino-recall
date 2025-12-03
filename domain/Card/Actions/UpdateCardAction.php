<?php

declare(strict_types=1);

namespace Domain\Card\Actions;

use Domain\Card\Data\CardData;
use Domain\Card\Enums\CardType;
use Domain\Card\Models\Card;

class UpdateCardAction
{
    public function execute(
        int $cardId,
        ?int $deckId = null,
        ?string $question = null,
        ?string $answer = null,
        ?string $image_path = null,
        ?CardType $cardType = null,
        ?array $answerChoices = null,
        ?int $correctAnswerIndex = null
    ): CardData {
        $card = Card::findOrFail($cardId);

        $updateData = [];

        if ($deckId !== null) {
            $updateData['deck_id'] = $deckId;
        }

        if ($question !== null) {
            $updateData['question'] = $question;
        }

        if ($answer !== null) {
            $updateData['answer'] = $answer;
        }

        if ($image_path !== null) {
            $updateData['image_path'] = $image_path;
        }

        if ($cardType !== null) {
            $updateData['card_type'] = $cardType->value;
        }

        if ($answerChoices !== null) {
            $updateData['answer_choices'] = json_encode($answerChoices);
        }

        if ($correctAnswerIndex !== null) {
            $updateData['correct_answer_index'] = $correctAnswerIndex;
        }

        // Validate multiple choice fields if card type is being set to multiple choice
        $finalCardType = $cardType ?? CardType::from($card->card_type);
        if ($finalCardType === CardType::MULTIPLE_CHOICE) {
            $finalAnswerChoices = $answerChoices ?? ($card->answer_choices ? json_decode($card->answer_choices, true) : null);
            $finalCorrectAnswerIndex = $correctAnswerIndex ?? $card->correct_answer_index;

            if ($finalAnswerChoices === null || count($finalAnswerChoices) < 2) {
                throw new \InvalidArgumentException('Multiple choice cards must have at least 2 answer choices.');
            }

            if ($finalCorrectAnswerIndex === null || $finalCorrectAnswerIndex < 0 || $finalCorrectAnswerIndex >= count($finalAnswerChoices)) {
                throw new \InvalidArgumentException('Multiple choice cards must have a valid correct answer index.');
            }
        }

        $card->update($updateData);

        return CardData::fromModel($card->fresh());
    }
}
