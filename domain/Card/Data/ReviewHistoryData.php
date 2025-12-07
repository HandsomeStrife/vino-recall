<?php

declare(strict_types=1);

namespace Domain\Card\Data;

use Domain\Card\Enums\SrsStage;
use Domain\Card\Models\ReviewHistory;
use Spatie\LaravelData\Data;

/**
 * DTO representing a single review event.
 */
class ReviewHistoryData extends Data
{
    public function __construct(
        public int $id,
        public int $user_id,
        public int $card_id,
        public bool $is_correct,
        public int $previous_stage,
        public int $new_stage,
        public bool $is_practice,
        public string $reviewed_at,
        public string $created_at,
        public string $updated_at,
    ) {}

    public static function fromModel(ReviewHistory $history): self
    {
        return new self(
            id: $history->id,
            user_id: $history->user_id,
            card_id: $history->card_id,
            is_correct: $history->is_correct,
            previous_stage: $history->previous_stage,
            new_stage: $history->new_stage,
            is_practice: $history->is_practice,
            reviewed_at: $history->reviewed_at->toDateTimeString(),
            created_at: $history->created_at->toDateTimeString(),
            updated_at: $history->updated_at->toDateTimeString(),
        );
    }

    /**
     * Get the previous stage name.
     */
    public function getPreviousStageName(): string
    {
        return SrsStage::fromStage($this->previous_stage)->getName();
    }

    /**
     * Get the new stage name.
     */
    public function getNewStageName(): string
    {
        return SrsStage::fromStage($this->new_stage)->getName();
    }
}

