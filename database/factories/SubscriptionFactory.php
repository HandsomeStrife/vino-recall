<?php

namespace Database\Factories;

use Domain\Subscription\Models\Plan;
use Domain\Subscription\Models\Subscription;
use Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Domain\Subscription\Models\Subscription>
 */
class SubscriptionFactory extends Factory
{
    protected $model = Subscription::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'plan_id' => Plan::factory(),
            'stripe_subscription_id' => fake()->uuid(),
            'status' => 'active',
            'current_period_end' => now()->addMonth(),
        ];
    }
}
