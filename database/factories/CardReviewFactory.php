<?php

namespace Database\Factories;

use Domain\Card\Enums\CardRating;
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
        $rating = fake()->randomElement(CardRating::cases());
        $isCorrect = $rating === CardRating::CORRECT;

        return [
            'user_id' => User::factory(),
            'card_id' => Card::factory(),
            'rating' => $rating->value,
            'is_correct' => $isCorrect,
            'is_practice' => false,
            'selected_answer' => null,
            'next_review_at' => now()->addDays(fake()->numberBetween(1, 7)),
            'ease_factor' => fake()->randomFloat(2, 1.3, 2.5),
        ];
    }
}
