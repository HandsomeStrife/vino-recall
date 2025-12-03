<?php

namespace Database\Factories;

use Domain\Deck\Models\Deck;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Domain\Deck\Models\Deck>
 */
class DeckFactory extends Factory
{
    protected $model = Deck::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'category' => fake()->optional()->randomElement(['red-wines', 'white-wines', 'regions', 'production', 'spirits']),
            'is_active' => true,
            'created_by' => null, // Can be set explicitly when creating
        ];
    }
}
