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
        $answerChoices = json_encode([
            $this->faker->word(),
            $this->faker->word(),
            $this->faker->word(),
            $this->faker->word(),
        ]);
        
        // Sometimes single correct answer, sometimes multiple
        $correctAnswerIndices = $this->faker->boolean(70)
            ? json_encode([$this->faker->numberBetween(0, 3)])
            : json_encode($this->faker->randomElements([0, 1, 2, 3], $this->faker->numberBetween(2, 3)));

        return [
            'deck_id' => Deck::factory(),
            'shortcode' => strtoupper(\Illuminate\Support\Str::random(6)),
            'card_type' => 'multiple_choice',
            'question' => fake()->sentence(),
            'answer' => fake()->paragraph(),
            'image_path' => null,
            'answer_choices' => $answerChoices,
            'correct_answer_indices' => $correctAnswerIndices,
        ];
    }

    public function singleCorrectAnswer(): static
    {
        return $this->state(fn (array $attributes) => [
            'card_type' => 'multiple_choice',
            'answer_choices' => json_encode([
                'Option A',
                'Option B',
                'Option C',
                'Option D',
            ]),
            'correct_answer_indices' => json_encode([1]),
        ]);
    }

    public function multipleCorrectAnswers(): static
    {
        return $this->state(fn (array $attributes) => [
            'card_type' => 'multiple_choice',
            'answer_choices' => json_encode([
                'Option A',
                'Option B',
                'Option C',
                'Option D',
            ]),
            'correct_answer_indices' => json_encode([0, 2]),
        ]);
    }
}
