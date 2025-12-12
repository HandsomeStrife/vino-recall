<?php

declare(strict_types=1);

use Domain\Subscription\Models\Plan;
use Domain\Subscription\Models\Subscription;
use Domain\User\Models\User;

test('stripe webhook route exists', function () {
    // Just verify the route is registered
    $routes = collect(Route::getRoutes())->map(fn ($route) => $route->getName());
    expect($routes)->toContain('webhook.stripe');
});

test('webhook endpoint is registered', function () {
    // Verify the webhook route exists and is accessible
    // Actual webhook testing requires valid Stripe signatures
    // which are tested in integration/staging environments

    expect(route('webhook.stripe'))->toContain('/webhook/stripe');
});

test('subscription data structure is correct', function () {
    $user = User::factory()->create();
    $plan = Plan::factory()->create(['name' => 'Test Plan']);

    $subscription = Subscription::factory()->create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'stripe_subscription_id' => 'sub_test123',
        'status' => 'active',
        'current_period_end' => now()->addMonth(),
    ]);

    expect($subscription->user_id)->toBe($user->id)
        ->and($subscription->plan_id)->toBe($plan->id)
        ->and($subscription->stripe_subscription_id)->toBe('sub_test123')
        ->and($subscription->status)->toBe('active')
        ->and($subscription->current_period_end)->not->toBeNull();
});

test('subscription status can be updated', function () {
    $user = User::factory()->create();
    $plan = Plan::factory()->create();
    $subscription = Subscription::factory()->create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'stripe_subscription_id' => 'sub_test123',
        'status' => 'active',
    ]);

    $subscription->update(['status' => 'past_due']);
    $subscription->refresh();

    expect($subscription->status)->toBe('past_due');
});

test('subscription can be canceled', function () {
    $user = User::factory()->create();
    $plan = Plan::factory()->create();
    $subscription = Subscription::factory()->create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'stripe_subscription_id' => 'sub_test123',
        'status' => 'active',
    ]);

    $subscription->update(['status' => 'canceled']);
    $subscription->refresh();

    expect($subscription->status)->toBe('canceled');
});

test('subscription can find by stripe subscription id', function () {
    $user = User::factory()->create();
    $plan = Plan::factory()->create();
    $subscription = Subscription::factory()->create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'stripe_subscription_id' => 'sub_unique123',
        'status' => 'active',
    ]);

    $found = Subscription::where('stripe_subscription_id', 'sub_unique123')->first();

    expect($found)->not->toBeNull()
        ->and($found->id)->toBe($subscription->id);
});

test('subscription period end can be updated', function () {
    $user = User::factory()->create();
    $plan = Plan::factory()->create();
    $subscription = Subscription::factory()->create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'current_period_end' => now()->addMonth(),
    ]);

    $newPeriodEnd = now()->addMonths(2);
    $subscription->update(['current_period_end' => $newPeriodEnd]);
    $subscription->refresh();

    expect($subscription->current_period_end->format('Y-m-d'))->toBe($newPeriodEnd->format('Y-m-d'));
});

test('multiple subscriptions can exist for different users', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $plan = Plan::factory()->create();

    $sub1 = Subscription::factory()->create([
        'user_id' => $user1->id,
        'plan_id' => $plan->id,
        'stripe_subscription_id' => 'sub_user1',
    ]);

    $sub2 = Subscription::factory()->create([
        'user_id' => $user2->id,
        'plan_id' => $plan->id,
        'stripe_subscription_id' => 'sub_user2',
    ]);

    expect(Subscription::count())->toBeGreaterThanOrEqual(2);
});
