<?php

namespace Database\Factories;

use Domain\Card\Models\Card;
use Domain\Deck\Models\Deck;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Domain\Card\Models\Card>
 */
class CardFactory extends Factory
{
    protected $model = Card::class;

    public function definition(): array
    {
        $cardType = $this->faker->randomElement(['traditional', 'multiple_choice']);
        
        $answerChoices = null;
        $correctAnswerIndex = null;
        
        if ($cardType === 'multiple_choice') {
            $answerChoices = json_encode([
                $this->faker->word(),
                $this->faker->word(),
                $this->faker->word(),
                $this->faker->word(),
            ]);
            $correctAnswerIndex = $this->faker->numberBetween(0, 3);
        }

        return [
            'deck_id' => Deck::factory(),
            'card_type' => $cardType,
            'question' => fake()->sentence(),
            'answer' => fake()->paragraph(),
            'image_path' => null,
            'answer_choices' => $answerChoices,
            'correct_answer_index' => $correctAnswerIndex,
        ];
    }

    public function traditional(): static
    {
        return $this->state(fn (array $attributes) => [
            'card_type' => 'traditional',
            'answer_choices' => null,
            'correct_answer_index' => null,
        ]);
    }

    public function multipleChoice(): static
    {
        return $this->state(fn (array $attributes) => [
            'card_type' => 'multiple_choice',
            'answer_choices' => json_encode([
                'Option A',
                'Option B',
                'Option C',
                'Option D',
            ]),
            'correct_answer_index' => 1,
        ]);
    }
}
