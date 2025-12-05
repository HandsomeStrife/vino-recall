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
        ?array $correctAnswerIndices = null
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

        // Always set card type to multiple choice
        $updateData['card_type'] = CardType::MULTIPLE_CHOICE->value;

        if ($answerChoices !== null) {
            $updateData['answer_choices'] = json_encode($answerChoices);
        }

        if ($correctAnswerIndices !== null) {
            $updateData['correct_answer_indices'] = json_encode($correctAnswerIndices);
        }

        // Validate card fields
        $finalAnswerChoices = $answerChoices ?? ($card->answer_choices ? json_decode($card->answer_choices, true) : null);
        $finalCorrectAnswerIndices = $correctAnswerIndices ?? ($card->correct_answer_indices ? json_decode($card->correct_answer_indices, true) : null);

        if ($finalAnswerChoices === null || count($finalAnswerChoices) < 2) {
            throw new \InvalidArgumentException('Cards must have at least 2 answer choices.');
        }

        if ($finalCorrectAnswerIndices === null || count($finalCorrectAnswerIndices) < 1) {
            throw new \InvalidArgumentException('Cards must have at least one correct answer index.');
        }

        // Validate all indices are within bounds
        foreach ($finalCorrectAnswerIndices as $index) {
            if ($index < 0 || $index >= count($finalAnswerChoices)) {
                throw new \InvalidArgumentException('Cards must have valid correct answer indices.');
            }
        }

        $card->update($updateData);

        return CardData::fromModel($card->fresh());
    }
}
