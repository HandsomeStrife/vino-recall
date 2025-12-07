<?php

namespace Database\Factories;

use Domain\Card\Enums\SrsStage;
use Domain\Card\Models\Card;
use Domain\Card\Models\CardReview;
use Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Domain\Card\Models\CardReview>
 */
class CardReviewFactory extends Factory
{
    protected $model = CardReview::class;

    public function definition(): array
    {
        $stage = fake()->numberBetween(SrsStage::STAGE_MIN, SrsStage::STAGE_MAX);
        $interval = SrsStage::intervalForStage($stage);

        return [
            'user_id' => User::factory(),
            'card_id' => Card::factory(),
            'srs_stage' => $stage,
            'next_review_at' => $interval !== null ? now()->add($interval) : null,
        ];
    }

    /**
     * Set the card to a new/unseen state (stage 0).
     */
    public function uncorked(): static
    {
        return $this->state(fn (array $attributes) => [
            'srs_stage' => SrsStage::STAGE_MIN,
            'next_review_at' => null,
        ]);
    }

    /**
     * Set the card to a mastered state (stage >= MASTERED_THRESHOLD).
     */
    public function mastered(): static
    {
        return $this->state(function (array $attributes) {
            $stage = fake()->numberBetween(SrsStage::MASTERED_THRESHOLD, SrsStage::STAGE_MAX);
            $interval = SrsStage::intervalForStage($stage);

            return [
                'srs_stage' => $stage,
                'next_review_at' => $interval !== null ? now()->add($interval) : null,
            ];
        });
    }

    /**
     * Set the card to a specific SRS stage.
     */
    public function atStage(int $stage): static
    {
        return $this->state(function (array $attributes) use ($stage) {
            $interval = SrsStage::intervalForStage($stage);

            return [
                'srs_stage' => $stage,
                'next_review_at' => $interval !== null ? now()->add($interval) : null,
            ];
        });
    }

    /**
     * Set the card as due for review.
     */
    public function due(): static
    {
        return $this->state(fn (array $attributes) => [
            'next_review_at' => now()->subHour(),
        ]);
    }

    /**
     * Set the card as Wine God (retired, no more reviews).
     */
    public function retired(): static
    {
        return $this->state(fn (array $attributes) => [
            'srs_stage' => SrsStage::STAGE_MAX,
            'next_review_at' => null,
        ]);
    }
}
