<?php

declare(strict_types=1);

use Domain\Subscription\Data\PlanData;
use Domain\Subscription\Models\Plan;
use Domain\Subscription\Repositories\PlanRepository;

test('plan repository can get all plans', function () {
    Plan::factory()->create(['name' => 'Basic Plan', 'price' => 9.99]);
    Plan::factory()->create(['name' => 'Premium Plan', 'price' => 19.99]);
    Plan::factory()->create(['name' => 'Enterprise Plan', 'price' => 49.99]);

    $repository = new PlanRepository();
    $result = $repository->getAll();

    expect($result)->toHaveCount(3)
        ->and($result->first())->toBeInstanceOf(PlanData::class);
});

test('plan repository returns empty collection when no plans exist', function () {
    $repository = new PlanRepository();
    $result = $repository->getAll();

    expect($result)->toBeEmpty();
});

test('plan repository can find plan by id', function () {
    $plan = Plan::factory()->create([
        'name' => 'Test Plan',
        'price' => 14.99,
        'features' => 'Test features',
    ]);

    $repository = new PlanRepository();
    $result = $repository->findById($plan->id);

    expect($result)->toBeInstanceOf(PlanData::class)
        ->and($result->id)->toBe($plan->id)
        ->and($result->name)->toBe('Test Plan')
        ->and($result->price)->toBe('14.99')
        ->and($result->features)->toBe('Test features');
});

test('plan repository returns null when plan not found by id', function () {
    $repository = new PlanRepository();
    $result = $repository->findById(999999);

    expect($result)->toBeNull();
});

test('plan repository preserves all plan data fields', function () {
    $plan = Plan::factory()->create([
        'name' => 'Full Plan',
        'price' => 29.99,
        'features' => 'All features included',
        'stripe_price_id' => 'price_test123',
    ]);

    $repository = new PlanRepository();
    $result = $repository->findById($plan->id);

    expect($result->name)->toBe('Full Plan')
        ->and($result->price)->toBe('29.99')
        ->and($result->features)->toBe('All features included')
        ->and($result->stripe_price_id)->toBe('price_test123');
});

test('plan repository returns plans in correct order', function () {
    Plan::factory()->create(['name' => 'Plan A', 'price' => 10.00]);
    Plan::factory()->create(['name' => 'Plan B', 'price' => 20.00]);
    Plan::factory()->create(['name' => 'Plan C', 'price' => 30.00]);

    $repository = new PlanRepository();
    $result = $repository->getAll();

    expect($result)->toHaveCount(3);
    $names = $result->pluck('name')->toArray();
    expect($names)->toContain('Plan A', 'Plan B', 'Plan C');
});

test('plan repository handles plans with null features', function () {
    $plan = Plan::factory()->create([
        'name' => 'Basic',
        'price' => 5.00,
        'features' => null,
    ]);

    $repository = new PlanRepository();
    $result = $repository->findById($plan->id);

    expect($result->features)->toBeNull();
});

test('plan repository handles plans with null stripe_price_id', function () {
    $plan = Plan::factory()->create([
        'name' => 'Free',
        'price' => 0.00,
        'stripe_price_id' => null,
    ]);

    $repository = new PlanRepository();
    $result = $repository->findById($plan->id);

    expect($result->stripe_price_id)->toBeNull();
});

