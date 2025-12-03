<?php

namespace Database\Factories;

use Domain\Subscription\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Domain\Subscription\Models\Plan>
 */
class PlanFactory extends Factory
{
    protected $model = Plan::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'stripe_price_id' => fake()->uuid(),
            'price' => fake()->randomFloat(2, 5, 50),
            'features' => fake()->sentence(),
        ];
    }
}
