<?php

declare(strict_types=1);

use Domain\Subscription\Actions\CancelSubscriptionAction;
use Domain\Subscription\Actions\CreateSubscriptionAction;
use Domain\Subscription\Actions\UpdateSubscriptionAction;
use Domain\Subscription\Models\Plan;
use Domain\Subscription\Models\Subscription;
use Domain\User\Models\User;

it('can create a new subscription', function () {
    $user = User::factory()->create();
    $plan = Plan::factory()->create();
    $action = new CreateSubscriptionAction;

    $subscriptionData = $action->execute(
        userId: $user->id,
        planId: $plan->id,
        stripeSubscriptionId: 'sub_test123',
        status: 'active'
    );

    expect($subscriptionData->user_id)->toBe($user->id)
        ->and($subscriptionData->plan_id)->toBe($plan->id)
        ->and($subscriptionData->status)->toBe('active')
        ->and($subscriptionData->stripe_subscription_id)->toBe('sub_test123');

    $this->assertDatabaseHas('subscriptions', [
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'status' => 'active',
    ]);
});

it('can update an existing subscription', function () {
    $subscription = Subscription::factory()->create(['status' => 'active']);
    $newPlan = Plan::factory()->create();
    $action = new UpdateSubscriptionAction;

    $updatedSubscriptionData = $action->execute(
        subscriptionId: $subscription->id,
        planId: $newPlan->id,
        status: 'past_due'
    );

    expect($updatedSubscriptionData->plan_id)->toBe($newPlan->id)
        ->and($updatedSubscriptionData->status)->toBe('past_due');

    $this->assertDatabaseHas('subscriptions', [
        'id' => $subscription->id,
        'plan_id' => $newPlan->id,
        'status' => 'past_due',
    ]);
});

it('can cancel a subscription', function () {
    $subscription = Subscription::factory()->create(['status' => 'active']);
    $action = new CancelSubscriptionAction(new UpdateSubscriptionAction);

    $action->execute($subscription->id);

    $this->assertDatabaseHas('subscriptions', [
        'id' => $subscription->id,
        'status' => 'cancelled',
    ]);
});

it('throws exception when updating non-existent subscription', function () {
    $action = new UpdateSubscriptionAction;
    $action->execute(999, planId: 1);
})->throws(Illuminate\Database\Eloquent\ModelNotFoundException::class);
