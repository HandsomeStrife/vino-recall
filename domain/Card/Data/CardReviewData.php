<?php

declare(strict_types=1);

namespace Domain\Card\Data;

use Domain\Card\Models\CardReview;
use Spatie\LaravelData\Data;

class CardReviewData extends Data
{
    public function __construct(
        public int $id,
        public int $user_id,
        public int $card_id,
        public string $rating,
        public ?string $selected_answer,
        public ?string $next_review_at,
        public string $ease_factor,
        public string $created_at,
        public string $updated_at,
    ) {}

    public static function fromModel(CardReview $review): self
    {
        return new self(
            id: $review->id,
            user_id: $review->user_id,
            card_id: $review->card_id,
            rating: $review->rating,
            selected_answer: $review->selected_answer ?? null,
            next_review_at: $review->next_review_at?->toDateTimeString(),
            ease_factor: (string) $review->ease_factor,
            created_at: $review->created_at->toDateTimeString(),
            updated_at: $review->updated_at->toDateTimeString(),
        );
    }
}
