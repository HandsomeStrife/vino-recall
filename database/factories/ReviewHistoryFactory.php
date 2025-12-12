<?php

namespace Database\Factories;

use Domain\Card\Enums\SrsStage;
use Domain\Card\Models\Card;
use Domain\Card\Models\ReviewHistory;
use Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Domain\Card\Models\ReviewHistory>
 */
class ReviewHistoryFactory extends Factory
{
    protected $model = ReviewHistory::class;

    public function definition(): array
    {
        $isCorrect = fake()->boolean(70); // 70% correct by default
        $previousStage = fake()->numberBetween(SrsStage::STAGE_MIN, SrsStage::STAGE_MAX - 1);
        $newStage = $isCorrect
            ? SrsStage::calculateNewStageOnCorrect($previousStage)
            : SrsStage::calculateNewStageOnIncorrect($previousStage);

        return [
            'user_id' => User::factory(),
            'card_id' => Card::factory(),
            'is_correct' => $isCorrect,
            'previous_stage' => $previousStage,
            'new_stage' => $newStage,
            'is_practice' => false,
            'reviewed_at' => now(),
        ];
    }

    /**
     * Create a correct review.
     */
    public function correct(): static
    {
        return $this->state(function (array $attributes) {
            $previousStage = $attributes['previous_stage'] ?? 0;

            return [
                'is_correct' => true,
                'new_stage' => SrsStage::calculateNewStageOnCorrect($previousStage),
            ];
        });
    }

    /**
     * Create an incorrect review.
     */
    public function incorrect(): static
    {
        return $this->state(function (array $attributes) {
            $previousStage = $attributes['previous_stage'] ?? 0;

            return [
                'is_correct' => false,
                'new_stage' => SrsStage::calculateNewStageOnIncorrect($previousStage),
            ];
        });
    }

    /**
     * Create a practice review (doesn't affect SRS).
     */
    public function practice(): static
    {
        return $this->state(function (array $attributes) {
            $previousStage = $attributes['previous_stage'] ?? 0;

            return [
                'is_practice' => true,
                'new_stage' => $previousStage, // Practice doesn't change stage
            ];
        });
    }

    /**
     * Set the review as happening at a specific time.
     */
    public function reviewedAt(\Carbon\Carbon $time): static
    {
        return $this->state(fn (array $attributes) => [
            'reviewed_at' => $time,
        ]);
    }

    /**
     * Set the review from a specific starting stage.
     */
    public function fromStage(int $stage): static
    {
        return $this->state(fn (array $attributes) => [
            'previous_stage' => $stage,
        ]);
    }
}
