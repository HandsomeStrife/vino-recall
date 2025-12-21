<?php

declare(strict_types=1);

namespace Database\Factories;

use Domain\Deck\Models\Deck;
use Domain\Material\Models\Material;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Material>
 */
class MaterialFactory extends Factory
{
    protected $model = Material::class;

    public function definition(): array
    {
        return [
            'deck_id' => Deck::factory(),
            'title' => fake()->sentence(),
            'content' => '<p>'.fake()->paragraphs(3, true).'</p>',
            'image_path' => null,
            'image_position' => 'top',
            'sort_order' => 0,
        ];
    }

    public function withImage(): self
    {
        return $this->state(fn (array $attributes) => [
            'image_path' => 'materials/test-image.jpg',
        ]);
    }

    public function atPosition(int $position): self
    {
        return $this->state(fn (array $attributes) => [
            'sort_order' => $position,
        ]);
    }
}
