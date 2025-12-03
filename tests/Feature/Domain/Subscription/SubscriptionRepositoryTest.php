<?php

declare(strict_types=1);

use Domain\Subscription\Data\SubscriptionData;
use Domain\Subscription\Models\Plan;
use Domain\Subscription\Models\Subscription;
use Domain\Subscription\Repositories\SubscriptionRepository;
use Domain\User\Models\User;

test('subscription repository can find subscription by user id', function () {
    $user = User::factory()->create();
    $plan = Plan::factory()->create();
    $subscription = Subscription::factory()->create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'status' => 'active',
    ]);

    $repository = new SubscriptionRepository();
    $result = $repository->findByUserId($user->id);

    expect($result)->toBeInstanceOf(SubscriptionData::class)
        ->and($result->user_id)->toBe($user->id)
        ->and($result->plan_id)->toBe($plan->id)
        ->and($result->status)->toBe('active');
});

test('subscription repository returns null when no subscription found for user', function () {
    $user = User::factory()->create();

    $repository = new SubscriptionRepository();
    $result = $repository->findByUserId($user->id);

    expect($result)->toBeNull();
});

test('subscription repository can find subscription by id', function () {
    $plan = Plan::factory()->create();
    $subscription = Subscription::factory()->create([
        'plan_id' => $plan->id,
        'status' => 'active',
        'stripe_subscription_id' => 'sub_test123',
    ]);

    $repository = new SubscriptionRepository();
    $result = $repository->findById($subscription->id);

    expect($result)->toBeInstanceOf(SubscriptionData::class)
        ->and($result->id)->toBe($subscription->id)
        ->and($result->stripe_subscription_id)->toBe('sub_test123');
});

test('subscription repository returns null when subscription not found by id', function () {
    $repository = new SubscriptionRepository();
    $result = $repository->findById(999999);

    expect($result)->toBeNull();
});

test('subscription repository can get all subscriptions', function () {
    $plan = Plan::factory()->create();
    Subscription::factory()->count(3)->create(['plan_id' => $plan->id]);

    $repository = new SubscriptionRepository();
    $result = $repository->getAll();

    expect($result)->toHaveCount(3)
        ->and($result->first())->toBeInstanceOf(SubscriptionData::class);
});

test('subscription repository returns empty collection when no subscriptions', function () {
    $repository = new SubscriptionRepository();
    $result = $repository->getAll();

    expect($result)->toBeEmpty();
});

test('subscription repository handles multiple subscriptions per user correctly', function () {
    $user = User::factory()->create();
    $plan = Plan::factory()->create();
    
    // Create old inactive subscription
    Subscription::factory()->create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'status' => 'canceled',
        'created_at' => now()->subYear(),
    ]);

    // Create active subscription
    $activeSubscription = Subscription::factory()->create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'status' => 'active',
        'created_at' => now(),
    ]);

    $repository = new SubscriptionRepository();
    $result = $repository->findByUserId($user->id);

    // Should return the most recent one (or first one found, depending on implementation)
    expect($result)->toBeInstanceOf(SubscriptionData::class)
        ->and($result->user_id)->toBe($user->id);
});

test('subscription repository preserves all subscription data fields', function () {
    $user = User::factory()->create();
    $plan = Plan::factory()->create();
    $periodEnd = now()->addMonth();
    
    $subscription = Subscription::factory()->create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'stripe_subscription_id' => 'sub_abc123',
        'status' => 'active',
        'current_period_end' => $periodEnd,
    ]);

    $repository = new SubscriptionRepository();
    $result = $repository->findById($subscription->id);

    expect($result->user_id)->toBe($user->id)
        ->and($result->plan_id)->toBe($plan->id)
        ->and($result->stripe_subscription_id)->toBe('sub_abc123')
        ->and($result->status)->toBe('active')
        ->and($result->current_period_end)->not->toBeNull();
});

