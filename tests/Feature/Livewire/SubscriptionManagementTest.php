<?php

declare(strict_types=1);

use App\Livewire\SubscriptionManagement;
use Domain\Subscription\Models\Plan;
use Domain\Subscription\Models\Subscription;
use Livewire\Livewire;

test('subscription management component can be rendered', function () {
    $user = actingAsUser();

    Livewire::test(SubscriptionManagement::class)
        ->assertStatus(200)
        ->assertSee('Subscription Management');
});

test('subscription management displays available plans', function () {
    $user = actingAsUser();
    Plan::factory()->create(['name' => 'Basic Plan', 'price' => 9.99]);
    Plan::factory()->create(['name' => 'Premium Plan', 'price' => 19.99]);

    Livewire::test(SubscriptionManagement::class)
        ->assertSee('Basic Plan')
        ->assertSee('Premium Plan')
        ->assertSee('$9.99')
        ->assertSee('$19.99');
});

test('subscription management displays current subscription when user has one', function () {
    $user = actingAsUser();
    $plan = Plan::factory()->create([
        'name' => 'Premium Plan',
        'price' => 19.99,
    ]);
    Subscription::factory()->create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'status' => 'active',
        'current_period_end' => now()->addMonth()->format('Y-m-d H:i:s'),
    ]);

    Livewire::test(SubscriptionManagement::class)
        ->assertSee('Current Subscription')
        ->assertSee('active');
});

test('subscription management displays plans when user has no subscription', function () {
    $user = actingAsUser();
    Plan::factory()->create(['name' => 'Basic Plan', 'price' => 9.99]);

    Livewire::test(SubscriptionManagement::class)
        ->assertDontSee('Current Subscription')
        ->assertSee('Basic Plan');
});

test('subscription management shows subscription status', function () {
    $user = actingAsUser();
    $plan = Plan::factory()->create(['name' => 'Test Plan']);
    Subscription::factory()->create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'status' => 'active',
    ]);

    Livewire::test(SubscriptionManagement::class)
        ->assertSee('Status:')
        ->assertSee('active');
});

test('subscription management displays plan features', function () {
    $user = actingAsUser();
    Plan::factory()->create([
        'name' => 'Feature Plan',
        'price' => 14.99,
        'features' => 'Access to premium content',
    ]);

    Livewire::test(SubscriptionManagement::class)
        ->assertSee('Access to premium content');
});

test('subscription management shows current subscription section', function () {
    $user = actingAsUser();
    $plan = Plan::factory()->create(['name' => 'My Plan']);
    Subscription::factory()->create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'status' => 'active',
    ]);

    Livewire::test(SubscriptionManagement::class)
        ->assertSee('Current Subscription');
});

test('subscription page can be accessed', function () {
    $user = actingAsUser();

    $response = $this->get(route('subscription'));
    $response->assertStatus(200);
});

test('subscription management displays inactive subscription status', function () {
    $user = actingAsUser();
    $plan = Plan::factory()->create(['name' => 'Test Plan']);
    Subscription::factory()->create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'status' => 'inactive',
    ]);

    Livewire::test(SubscriptionManagement::class)
        ->assertSee('inactive');
});

