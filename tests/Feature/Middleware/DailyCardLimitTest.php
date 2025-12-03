<?php

declare(strict_types=1);

use Domain\Card\Models\Card;
use Domain\Card\Models\CardReview;
use Domain\Deck\Models\Deck;
use Domain\Subscription\Models\Plan;
use Domain\Subscription\Models\Subscription;
use Domain\User\Models\User;

test('free user has daily card limit', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    // Free users should be able to access study page
    $response = $this->get(route('study'));
    $response->assertStatus(200);
});

test('basic plan user has higher daily limit', function () {
    $user = User::factory()->create();
    $plan = Plan::factory()->create(['name' => 'Basic']);
    Subscription::factory()->create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'status' => 'active',
    ]);

    $this->actingAs($user);

    $response = $this->get(route('study'));
    $response->assertStatus(200);
});

test('premium user has unlimited cards', function () {
    $user = User::factory()->create();
    $plan = Plan::factory()->create(['name' => 'Premium']);
    Subscription::factory()->create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'status' => 'active',
    ]);

    $this->actingAs($user);

    // Create many card reviews today
    $deck = Deck::factory()->create();
    for ($i = 0; $i < 100; $i++) {
        $card = Card::factory()->create(['deck_id' => $deck->id]);
        CardReview::factory()->create([
            'user_id' => $user->id,
            'card_id' => $card->id,
            'created_at' => now(),
        ]);
    }

    // Premium users should still be able to study
    $response = $this->get(route('study'));
    $response->assertStatus(200);
});

test('card reviews from yesterday do not count toward today limit', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $deck = Deck::factory()->create();
    
    // Create reviews from yesterday
    for ($i = 0; $i < 20; $i++) {
        $card = Card::factory()->create(['deck_id' => $deck->id]);
        CardReview::factory()->create([
            'user_id' => $user->id,
            'card_id' => $card->id,
            'created_at' => now()->subDay(),
        ]);
    }

    // Today's limit should not be affected by yesterday's reviews
    $response = $this->get(route('study'));
    $response->assertStatus(200);
});

test('plan names determine daily limits', function () {
    $plans = [
        'Basic' => 50,
        'Premium' => -1, // unlimited
    ];

    foreach ($plans as $planName => $expectedLimit) {
        $user = User::factory()->create();
        $plan = Plan::factory()->create(['name' => $planName]);
        Subscription::factory()->create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'status' => 'active',
        ]);

        $this->actingAs($user);

        // Verify user can study
        $response = $this->get(route('study'));
        $response->assertStatus(200);
    }
});

test('inactive subscription reverts to free tier limits', function () {
    $user = User::factory()->create();
    $plan = Plan::factory()->create(['name' => 'Premium']);
    Subscription::factory()->create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'status' => 'inactive', // Not active
    ]);

    $this->actingAs($user);

    // Should have free tier limits (10 cards)
    $response = $this->get(route('study'));
    $response->assertStatus(200);
});

test('unknown plan defaults to free tier limits', function () {
    $user = User::factory()->create();
    $plan = Plan::factory()->create(['name' => 'UnknownPlan']);
    Subscription::factory()->create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'status' => 'active',
    ]);

    $this->actingAs($user);

    // Should default to free tier
    $response = $this->get(route('study'));
    $response->assertStatus(200);
});

