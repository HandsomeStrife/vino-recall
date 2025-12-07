<?php

declare(strict_types=1);

namespace Domain\Card\Data;

use Domain\Card\Enums\SrsStage;
use Domain\Card\Models\CardReview;
use Spatie\LaravelData\Data;

/**
 * DTO representing the SRS state for a user-card pair.
 */
class CardReviewData extends Data
{
    public function __construct(
        public int $id,
        public int $user_id,
        public int $card_id,
        public int $srs_stage,
        public ?string $next_review_at,
        public string $created_at,
        public string $updated_at,
    ) {}

    public static function fromModel(CardReview $review): self
    {
        return new self(
            id: $review->id,
            user_id: $review->user_id,
            card_id: $review->card_id,
            srs_stage: $review->srs_stage,
            next_review_at: $review->next_review_at?->toDateTimeString(),
            created_at: $review->created_at->toDateTimeString(),
            updated_at: $review->updated_at->toDateTimeString(),
        );
    }

    /**
     * Check if this card is mastered.
     */
    public function isMastered(): bool
    {
        return SrsStage::isMastered($this->srs_stage);
    }

    /**
     * Get the SrsStage enum for this card.
     */
    public function getSrsStageEnum(): SrsStage
    {
        return SrsStage::fromStage($this->srs_stage);
    }

    /**
     * Get the stage name.
     */
    public function getStageName(): string
    {
        return $this->getSrsStageEnum()->getName();
    }
}
