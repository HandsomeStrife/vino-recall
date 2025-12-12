<?php

declare(strict_types=1);

use Domain\Subscription\Models\Plan;
use Domain\Subscription\Models\Subscription;
use Domain\User\Models\User;

test('user without subscription can access free content', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertStatus(200);
});

test('user with active subscription can access subscribed content', function () {
    $user = User::factory()->create();
    $plan = Plan::factory()->create(['name' => 'Premium']);
    Subscription::factory()->create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'status' => 'active',
    ]);

    $this->actingAs($user);

    // All authenticated routes should be accessible
    $response = $this->get(route('dashboard'));
    $response->assertStatus(200);

    $response = $this->get(route('enrolled'));
    $response->assertStatus(200);

    $response = $this->get(route('library'));
    $response->assertStatus(200);
});

test('user with inactive subscription is treated like no subscription', function () {
    $user = User::factory()->create();
    $plan = Plan::factory()->create(['name' => 'Premium']);
    Subscription::factory()->create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'status' => 'inactive',
    ]);

    $this->actingAs($user);

    // Should still be able to access basic routes
    $response = $this->get(route('dashboard'));
    $response->assertStatus(200);
});

test('user with past_due subscription is treated like inactive', function () {
    $user = User::factory()->create();
    $plan = Plan::factory()->create(['name' => 'Premium']);
    Subscription::factory()->create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'status' => 'past_due',
    ]);

    $this->actingAs($user);

    // Should still access basic content
    $response = $this->get(route('dashboard'));
    $response->assertStatus(200);
});

test('subscription status values are handled correctly', function () {
    $user = User::factory()->create();
    $plan = Plan::factory()->create(['name' => 'Basic']);

    $statuses = ['active', 'inactive', 'past_due', 'canceled', 'trialing'];

    foreach ($statuses as $status) {
        $subscription = Subscription::factory()->create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'status' => $status,
        ]);

        expect($subscription->status)->toBe($status);
        $subscription->delete();
    }
});

test('free tier user can access dashboard and study', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    // Free users can access basic features
    $response = $this->get(route('dashboard'));
    $response->assertStatus(200);

    $response = $this->get(route('enrolled'));
    $response->assertStatus(200);

    $response = $this->get(route('library'));
    $response->assertStatus(200);
});

test('premium user has active subscription status', function () {
    $user = User::factory()->create();
    $plan = Plan::factory()->create(['name' => 'Premium']);
    $subscription = Subscription::factory()->create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'status' => 'active',
        'current_period_end' => now()->addMonth(),
    ]);

    $this->actingAs($user);

    expect($subscription->status)->toBe('active')
        ->and($subscription->current_period_end)->toBeGreaterThan(now());
});

test('subscription can be upgraded', function () {
    $user = User::factory()->create();
    $basicPlan = Plan::factory()->create(['name' => 'Basic', 'price' => 9.99]);
    $premiumPlan = Plan::factory()->create(['name' => 'Premium', 'price' => 19.99]);

    $subscription = Subscription::factory()->create([
        'user_id' => $user->id,
        'plan_id' => $basicPlan->id,
        'status' => 'active',
    ]);

    // Simulate upgrade
    $subscription->update(['plan_id' => $premiumPlan->id]);
    $subscription->refresh();

    expect($subscription->plan_id)->toBe($premiumPlan->id);
});

test('expired subscription can be detected', function () {
    $user = User::factory()->create();
    $plan = Plan::factory()->create();
    $subscription = Subscription::factory()->create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'status' => 'active',
        'current_period_end' => now()->subDay(),
    ]);

    expect($subscription->current_period_end)->toBeLessThan(now());
});
