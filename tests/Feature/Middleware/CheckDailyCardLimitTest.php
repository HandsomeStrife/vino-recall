<?php

declare(strict_types=1);

use App\Http\Middleware\CheckDailyCardLimit;
use Domain\Card\Models\Card;
use Domain\Card\Models\CardReview;
use Domain\Deck\Actions\EnrollUserInDeckAction;
use Domain\Deck\Models\Deck;
use Domain\Subscription\Models\Plan;
use Domain\Subscription\Models\Subscription;
use Domain\User\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

test('middleware allows access when under free tier limit', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $deck = Deck::factory()->create();
    $cards = Card::factory()->count(5)->create(['deck_id' => $deck->id]);

    // Enroll user in deck (required for reviews to count)
    (new EnrollUserInDeckAction)->execute($user->id, $deck->id);

    // Create 5 reviews today (under 10 free limit)
    foreach ($cards as $card) {
        CardReview::factory()->create([
            'user_id' => $user->id,
            'card_id' => $card->id,
            'created_at' => now(),
        ]);
    }

    $request = Request::create('/test', 'GET');
    $middleware = app(CheckDailyCardLimit::class);

    $response = $middleware->handle($request, fn () => new Response('OK'));

    expect($response->getContent())->toBe('OK');
});

test('middleware redirects when free tier limit reached', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $deck = Deck::factory()->create();
    (new EnrollUserInDeckAction)->execute($user->id, $deck->id);

    // Create 10 reviews today (at free limit)
    for ($i = 0; $i < 10; $i++) {
        $card = Card::factory()->create(['deck_id' => $deck->id]);
        CardReview::factory()->create([
            'user_id' => $user->id,
            'card_id' => $card->id,
            'created_at' => now(),
        ]);
    }

    $request = Request::create('/test', 'GET');
    $middleware = app(CheckDailyCardLimit::class);

    $response = $middleware->handle($request, fn () => new Response('OK'));

    expect($response->isRedirect())->toBeTrue();
});

test('middleware allows premium users unlimited access', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $plan = Plan::factory()->create(['name' => 'Premium']);
    Subscription::factory()->create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'status' => 'active',
    ]);

    $deck = Deck::factory()->create();
    (new EnrollUserInDeckAction)->execute($user->id, $deck->id);

    // Create 100 reviews today
    for ($i = 0; $i < 100; $i++) {
        $card = Card::factory()->create(['deck_id' => $deck->id]);
        CardReview::factory()->create([
            'user_id' => $user->id,
            'card_id' => $card->id,
            'created_at' => now(),
        ]);
    }

    $request = Request::create('/test', 'GET');
    $middleware = app(CheckDailyCardLimit::class);

    $response = $middleware->handle($request, fn () => new Response('OK'));

    expect($response->getContent())->toBe('OK');
});

test('middleware allows basic plan users up to 50 cards per day', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $plan = Plan::factory()->create(['name' => 'Basic']);
    Subscription::factory()->create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'status' => 'active',
    ]);

    $deck = Deck::factory()->create();
    (new EnrollUserInDeckAction)->execute($user->id, $deck->id);

    // Create 49 reviews today (under 50 basic limit)
    for ($i = 0; $i < 49; $i++) {
        $card = Card::factory()->create(['deck_id' => $deck->id]);
        CardReview::factory()->create([
            'user_id' => $user->id,
            'card_id' => $card->id,
            'created_at' => now(),
        ]);
    }

    $request = Request::create('/test', 'GET');
    $middleware = app(CheckDailyCardLimit::class);

    $response = $middleware->handle($request, fn () => new Response('OK'));

    expect($response->getContent())->toBe('OK');
});

test('middleware redirects basic plan users at 50 cards', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $plan = Plan::factory()->create(['name' => 'Basic']);
    Subscription::factory()->create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'status' => 'active',
    ]);

    $deck = Deck::factory()->create();
    (new EnrollUserInDeckAction)->execute($user->id, $deck->id);

    // Create 50 reviews today (at basic limit)
    for ($i = 0; $i < 50; $i++) {
        $card = Card::factory()->create(['deck_id' => $deck->id]);
        CardReview::factory()->create([
            'user_id' => $user->id,
            'card_id' => $card->id,
            'created_at' => now(),
        ]);
    }

    $request = Request::create('/test', 'GET');
    $middleware = app(CheckDailyCardLimit::class);

    $response = $middleware->handle($request, fn () => new Response('OK'));

    expect($response->isRedirect())->toBeTrue();
});

test('middleware ignores yesterday reviews', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $deck = Deck::factory()->create();
    (new EnrollUserInDeckAction)->execute($user->id, $deck->id);

    // Create 20 reviews yesterday
    for ($i = 0; $i < 20; $i++) {
        $card = Card::factory()->create(['deck_id' => $deck->id]);
        CardReview::factory()->create([
            'user_id' => $user->id,
            'card_id' => $card->id,
            'created_at' => now()->subDay(),
        ]);
    }

    $request = Request::create('/test', 'GET');
    $middleware = app(CheckDailyCardLimit::class);

    $response = $middleware->handle($request, fn () => new Response('OK'));

    expect($response->getContent())->toBe('OK');
});

test('middleware treats inactive subscription as free tier', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $plan = Plan::factory()->create(['name' => 'Premium']);
    Subscription::factory()->create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'status' => 'canceled',
    ]);

    $deck = Deck::factory()->create();
    (new EnrollUserInDeckAction)->execute($user->id, $deck->id);

    // Create 10 reviews today (at free limit)
    for ($i = 0; $i < 10; $i++) {
        $card = Card::factory()->create(['deck_id' => $deck->id]);
        CardReview::factory()->create([
            'user_id' => $user->id,
            'card_id' => $card->id,
            'created_at' => now(),
        ]);
    }

    $request = Request::create('/test', 'GET');
    $middleware = app(CheckDailyCardLimit::class);

    $response = $middleware->handle($request, fn () => new Response('OK'));

    expect($response->isRedirect())->toBeTrue();
});
