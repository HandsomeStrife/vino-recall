<?php

namespace Database\Factories;

use Domain\Deck\Models\Deck;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Domain\Deck\Models\Deck>
 */
class DeckFactory extends Factory
{
    protected $model = Deck::class;

    public function definition(): array
    {
        return [
            'identifier' => $this->generateUniqueIdentifier(),
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'is_active' => true,
            'created_by' => null, // Can be set explicitly when creating
        ];
    }

    private function generateUniqueIdentifier(): string
    {
        do {
            $identifier = strtoupper(Str::random(8));
        } while (Deck::where('identifier', $identifier)->exists());

        return $identifier;
    }
}
