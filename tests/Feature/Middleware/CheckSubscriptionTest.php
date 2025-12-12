<?php

declare(strict_types=1);

use App\Http\Middleware\CheckSubscription;
use Domain\Subscription\Models\Plan;
use Domain\Subscription\Models\Subscription;
use Domain\User\Models\User;
use Illuminate\Http\Request;

test('no required plans allows access without subscription', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $request = Request::create('/test', 'GET');
    $middleware = app(CheckSubscription::class);

    $response = $middleware->handle($request, function ($req) {
        return response('OK');
    }); // No required plans

    expect($response->getContent())->toBe('OK');
});

test('user without subscription is redirected when plan required', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $request = Request::create('/premium-content', 'GET');
    $middleware = app(CheckSubscription::class);

    $response = $middleware->handle($request, function ($req) {
        return response('OK');
    }, 'Premium');

    expect($response->isRedirect())->toBeTrue()
        ->and($response->getTargetUrl())->toContain('subscription');
});

test('user with inactive subscription is redirected', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $plan = Plan::factory()->create(['name' => 'Premium']);
    Subscription::factory()->create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'status' => 'canceled',
    ]);

    $request = Request::create('/premium-content', 'GET');
    $middleware = app(CheckSubscription::class);

    $response = $middleware->handle($request, function ($req) {
        return response('OK');
    }, 'Premium');

    expect($response->isRedirect())->toBeTrue();
});

test('user with active correct plan can access', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $plan = Plan::factory()->create(['name' => 'Premium']);
    Subscription::factory()->create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'status' => 'active',
    ]);

    $request = Request::create('/premium-content', 'GET');
    $middleware = app(CheckSubscription::class);

    $response = $middleware->handle($request, function ($req) {
        return response('OK');
    }, 'Premium');

    expect($response->getContent())->toBe('OK');
});

test('user with wrong plan is redirected', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $basicPlan = Plan::factory()->create(['name' => 'Basic']);
    Subscription::factory()->create([
        'user_id' => $user->id,
        'plan_id' => $basicPlan->id,
        'status' => 'active',
    ]);

    $request = Request::create('/premium-content', 'GET');
    $middleware = app(CheckSubscription::class);

    // Requires Premium, but user has Basic
    $response = $middleware->handle($request, function ($req) {
        return response('OK');
    }, 'Premium');

    expect($response->isRedirect())->toBeTrue();
});

test('middleware accepts multiple allowed plans', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $basicPlan = Plan::factory()->create(['name' => 'Basic']);
    Subscription::factory()->create([
        'user_id' => $user->id,
        'plan_id' => $basicPlan->id,
        'status' => 'active',
    ]);

    $request = Request::create('/content', 'GET');
    $middleware = app(CheckSubscription::class);

    // Accepts Basic OR Premium
    $response = $middleware->handle($request, function ($req) {
        return response('OK');
    }, 'Basic', 'Premium');

    expect($response->getContent())->toBe('OK');
});

test('premium user can access basic content', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $premiumPlan = Plan::factory()->create(['name' => 'Premium']);
    Subscription::factory()->create([
        'user_id' => $user->id,
        'plan_id' => $premiumPlan->id,
        'status' => 'active',
    ]);

    $request = Request::create('/basic-content', 'GET');
    $middleware = app(CheckSubscription::class);

    // Accepts Basic OR Premium
    $response = $middleware->handle($request, function ($req) {
        return response('OK');
    }, 'Basic', 'Premium');

    expect($response->getContent())->toBe('OK');
});

test('past_due subscription is treated as inactive', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $plan = Plan::factory()->create(['name' => 'Premium']);
    Subscription::factory()->create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'status' => 'past_due',
    ]);

    $request = Request::create('/premium-content', 'GET');
    $middleware = app(CheckSubscription::class);

    $response = $middleware->handle($request, function ($req) {
        return response('OK');
    }, 'Premium');

    expect($response->isRedirect())->toBeTrue();
});

test('subscription with deleted plan is redirected', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    // Create subscription with a plan that will be deleted
    $plan = Plan::factory()->create(['name' => 'OldPlan']);
    $subscription = Subscription::factory()->create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'status' => 'active',
    ]);

    // Delete the plan
    $plan->delete();

    $request = Request::create('/content', 'GET');
    $middleware = app(CheckSubscription::class);

    $response = $middleware->handle($request, function ($req) {
        return response('OK');
    }, 'Premium');

    expect($response->isRedirect())->toBeTrue();
});
