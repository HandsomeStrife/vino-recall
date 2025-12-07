<?php

namespace Database\Factories;

use Domain\Admin\Models\Admin;
use Domain\Category\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Domain\Category\Models\Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'description' => fake()->sentence(10),
            'image_path' => null,
            'is_active' => true,
            'created_by' => Admin::factory(),
        ];
    }

    public function inactive(): self
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function withImage(): self
    {
        return $this->state(fn (array $attributes) => [
            'image_path' => 'categories/test-image.jpg',
        ]);
    }
}
