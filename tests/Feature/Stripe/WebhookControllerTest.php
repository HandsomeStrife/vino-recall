<?php

declare(strict_types=1);

use Domain\Subscription\Actions\CancelSubscriptionAction;
use Domain\Subscription\Actions\CreateSubscriptionAction;
use Domain\Subscription\Actions\UpdateSubscriptionAction;
use Domain\Subscription\Models\Plan;
use Domain\Subscription\Models\Subscription;
use Domain\User\Models\User;

/**
 * Tests for subscription actions used by the webhook controller.
 * The WebhookController itself requires Stripe API keys which aren't available in testing.
 * These tests verify the underlying actions work correctly.
 */

// Update Subscription Action Tests
test('update subscription action updates status', function () {
    $user = User::factory()->create();
    $plan = Plan::factory()->create(['name' => 'Premium']);
    
    $subscription = Subscription::factory()->create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'stripe_subscription_id' => 'sub_test123',
        'status' => 'active',
    ]);
    
    $updateAction = app(UpdateSubscriptionAction::class);
    $updateAction->execute(
        subscriptionId: $subscription->id,
        status: 'past_due'
    );
    
    $subscription->refresh();
    
    expect($subscription->status)->toBe('past_due');
});

test('update subscription action updates period end', function () {
    $user = User::factory()->create();
    $plan = Plan::factory()->create(['name' => 'Premium']);
    
    $subscription = Subscription::factory()->create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'stripe_subscription_id' => 'sub_test456',
        'status' => 'active',
    ]);
    
    $newPeriodEnd = now()->addMonth()->toDateTimeString();
    
    $updateAction = app(UpdateSubscriptionAction::class);
    $updateAction->execute(
        subscriptionId: $subscription->id,
        currentPeriodEnd: $newPeriodEnd
    );
    
    $subscription->refresh();
    
    expect($subscription->current_period_end)->not->toBeNull();
});

test('update subscription action is idempotent', function () {
    $user = User::factory()->create();
    $plan = Plan::factory()->create(['name' => 'Premium']);
    
    $subscription = Subscription::factory()->create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'stripe_subscription_id' => 'sub_idempotent',
        'status' => 'active',
    ]);
    
    $updateAction = app(UpdateSubscriptionAction::class);
    
    // Update multiple times with same data
    $updateAction->execute(
        subscriptionId: $subscription->id,
        status: 'active'
    );
    
    $updateAction->execute(
        subscriptionId: $subscription->id,
        status: 'active'
    );
    
    $subscription->refresh();
    
    expect($subscription->status)->toBe('active');
});

test('update subscription action can change plan', function () {
    $user = User::factory()->create();
    $basicPlan = Plan::factory()->create(['name' => 'Basic']);
    $premiumPlan = Plan::factory()->create(['name' => 'Premium']);
    
    $subscription = Subscription::factory()->create([
        'user_id' => $user->id,
        'plan_id' => $basicPlan->id,
        'status' => 'active',
    ]);
    
    $updateAction = app(UpdateSubscriptionAction::class);
    $updateAction->execute(
        subscriptionId: $subscription->id,
        planId: $premiumPlan->id
    );
    
    $subscription->refresh();
    
    expect($subscription->plan_id)->toBe($premiumPlan->id);
});

// Cancel Subscription Action Tests
test('cancel subscription action updates status to cancelled', function () {
    $user = User::factory()->create();
    $plan = Plan::factory()->create(['name' => 'Premium']);
    
    $subscription = Subscription::factory()->create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'stripe_subscription_id' => 'sub_cancel123',
        'status' => 'active',
    ]);
    
    $cancelAction = app(CancelSubscriptionAction::class);
    $cancelAction->execute($subscription->id);
    
    $subscription->refresh();
    
    expect($subscription->status)->toBe('cancelled');
});

// Create Subscription Action Tests
test('create subscription action creates new subscription', function () {
    $user = User::factory()->create();
    $plan = Plan::factory()->create([
        'name' => 'Premium',
        'stripe_price_id' => 'price_test123',
    ]);
    
    $createAction = app(CreateSubscriptionAction::class);
    $subscriptionData = $createAction->execute(
        userId: $user->id,
        planId: $plan->id,
        stripeSubscriptionId: 'sub_new123',
        status: 'active',
        currentPeriodEnd: now()->addMonth()->toDateTimeString()
    );
    
    expect($subscriptionData->user_id)->toBe($user->id)
        ->and($subscriptionData->plan_id)->toBe($plan->id)
        ->and($subscriptionData->status)->toBe('active');
});

test('create subscription action with minimal params', function () {
    $user = User::factory()->create();
    $plan = Plan::factory()->create(['name' => 'Basic']);
    
    $createAction = app(CreateSubscriptionAction::class);
    $subscriptionData = $createAction->execute(
        userId: $user->id,
        planId: $plan->id
    );
    
    expect($subscriptionData->user_id)->toBe($user->id)
        ->and($subscriptionData->status)->toBe('active'); // Default
});

// Subscription Lookup Tests
test('subscription lookup by stripe id returns null when not found', function () {
    $subscription = Subscription::where('stripe_subscription_id', 'sub_nonexistent')->first();
    
    expect($subscription)->toBeNull();
});

test('subscription lookup by stripe id returns subscription when found', function () {
    $user = User::factory()->create();
    $plan = Plan::factory()->create(['name' => 'Premium']);
    
    Subscription::factory()->create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'stripe_subscription_id' => 'sub_found123',
        'status' => 'active',
    ]);
    
    $subscription = Subscription::where('stripe_subscription_id', 'sub_found123')->first();
    
    expect($subscription)->not->toBeNull()
        ->and($subscription->stripe_subscription_id)->toBe('sub_found123');
});

// Subscription Status Tests
test('subscription transitions from active to past_due', function () {
    $user = User::factory()->create();
    $plan = Plan::factory()->create(['name' => 'Premium']);
    
    $subscription = Subscription::factory()->create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'status' => 'active',
    ]);
    
    $updateAction = app(UpdateSubscriptionAction::class);
    $updateAction->execute(
        subscriptionId: $subscription->id,
        status: 'past_due'
    );
    
    $subscription->refresh();
    expect($subscription->status)->toBe('past_due');
    
    // Then back to active
    $updateAction->execute(
        subscriptionId: $subscription->id,
        status: 'active'
    );
    
    $subscription->refresh();
    expect($subscription->status)->toBe('active');
});
