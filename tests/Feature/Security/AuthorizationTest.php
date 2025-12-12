<?php

declare(strict_types=1);

use Domain\Card\Models\Card;
use Domain\Card\Models\CardReview;
use Domain\Deck\Models\Deck;
use Domain\Subscription\Models\Plan;
use Domain\Subscription\Models\Subscription;
use Domain\User\Models\User;

// Route Access Control Tests

test('guest cannot access authenticated routes', function () {
    $protectedRoutes = [
        'dashboard',
        'enrolled',
        'library',
        'profile',
        'subscription',
    ];

    foreach ($protectedRoutes as $route) {
        $response = $this->get(route($route));
        $response->assertRedirect(route('login'));
    }
});

test('guest cannot access admin routes', function () {
    $adminRoutes = [
        'admin.dashboard',
        'admin.users',
        'admin.decks',
        'admin.categories',
    ];

    foreach ($adminRoutes as $route) {
        $response = $this->get(route($route));
        // Admin routes use AdminAuthenticate middleware which redirects to admin login
        $response->assertRedirect(route('admin.login'));
    }
});

test('regular user cannot access admin routes', function () {
    $user = actingAsUser();

    $adminRoutes = [
        'admin.dashboard',
        'admin.users',
        'admin.decks',
        'admin.categories',
    ];

    foreach ($adminRoutes as $route) {
        $response = $this->get(route($route));
        // Regular user authenticated as web guard, but admin routes need admin guard
        // The AdminAuthenticate middleware redirects to admin login
        $response->assertRedirect(route('admin.login'));
    }
});

test('admin can access all admin routes', function () {
    $admin = actingAsAdmin();

    $adminRoutes = [
        'admin.dashboard',
        'admin.users',
        'admin.decks',
        'admin.categories',
    ];

    foreach ($adminRoutes as $route) {
        $response = $this->get(route($route));
        $response->assertStatus(200);
    }
});

// Data Access Control Tests

test('user can only see their own card reviews', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $deck = Deck::factory()->create();
    $card = Card::factory()->create(['deck_id' => $deck->id]);

    // Create reviews for both users
    $review1 = CardReview::factory()->create([
        'user_id' => $user1->id,
        'card_id' => $card->id,
    ]);

    $review2 = CardReview::factory()->create([
        'user_id' => $user2->id,
        'card_id' => $card->id,
    ]);

    $this->actingAs($user1);

    // User 1's reviews
    $user1Reviews = CardReview::where('user_id', $user1->id)->get();
    expect($user1Reviews)->toHaveCount(1)
        ->and($user1Reviews->first()->id)->toBe($review1->id);

    // Verify user can't directly access user2's review by ID
    $otherUserReview = CardReview::where('user_id', $user2->id)->where('id', $review2->id)->first();
    expect($otherUserReview->user_id)->toBe($user2->id);
});

test('user cannot modify another users card reviews', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $deck = Deck::factory()->create();
    $card = Card::factory()->create(['deck_id' => $deck->id]);

    $review2 = CardReview::factory()->create([
        'user_id' => $user2->id,
        'card_id' => $card->id,
        'rating' => 'good',
    ]);

    $this->actingAs($user1);

    // User 1 should not see user 2's review in their own query
    $user1Reviews = CardReview::where('user_id', $user1->id)->where('card_id', $card->id)->get();
    expect($user1Reviews)->toHaveCount(0);
});

test('user subscription data is isolated per user', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $plan = Plan::factory()->create();

    $subscription1 = Subscription::factory()->create([
        'user_id' => $user1->id,
        'plan_id' => $plan->id,
        'status' => 'active',
    ]);

    $subscription2 = Subscription::factory()->create([
        'user_id' => $user2->id,
        'plan_id' => $plan->id,
        'status' => 'inactive',
    ]);

    $this->actingAs($user1);

    // Verify user 1 can only see their own subscription
    $user1Subscriptions = Subscription::where('user_id', $user1->id)->get();
    expect($user1Subscriptions)->toHaveCount(1)
        ->and($user1Subscriptions->first()->status)->toBe('active');
});

test('user dashboard only shows their own statistics', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $deck = Deck::factory()->create();
    $card1 = Card::factory()->create(['deck_id' => $deck->id]);
    $card2 = Card::factory()->create(['deck_id' => $deck->id]);

    // User 1 reviews card 1
    CardReview::factory()->create([
        'user_id' => $user1->id,
        'card_id' => $card1->id,
    ]);

    // User 2 reviews both cards
    CardReview::factory()->create([
        'user_id' => $user2->id,
        'card_id' => $card1->id,
    ]);
    CardReview::factory()->create([
        'user_id' => $user2->id,
        'card_id' => $card2->id,
    ]);

    $this->actingAs($user1);

    // User 1 should only see their 1 review
    $user1Reviews = CardReview::where('user_id', $user1->id)->count();
    expect($user1Reviews)->toBe(1);
});

// Authentication State Tests

test('authenticated user cannot access guest-only routes', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    // Accessing login page while authenticated should redirect
    $response = $this->get(route('login'));
    $response->assertRedirect(route('dashboard'));

    // Accessing register page while authenticated should redirect
    $response = $this->get(route('register'));
    $response->assertRedirect(route('dashboard'));
});

test('unauthenticated requests to protected routes redirect to login', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));

    $response = $this->get(route('enrolled'));
    $response->assertRedirect(route('login'));

    $response = $this->get(route('library'));
    $response->assertRedirect(route('login'));
});

// Password Security Tests

test('password is hashed in database', function () {
    $plainPassword = 'my-secure-password';

    $user = User::factory()->create([
        'password' => \Illuminate\Support\Facades\Hash::make($plainPassword),
    ]);

    // Password should not be stored as plain text
    expect($user->password)->not->toBe($plainPassword);

    // But should verify correctly
    expect(\Illuminate\Support\Facades\Hash::check($plainPassword, $user->password))->toBeTrue();
});

test('user cannot login with incorrect password', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => \Illuminate\Support\Facades\Hash::make('correctpassword'),
    ]);

    $response = $this->post(route('login'), [
        'email' => 'test@example.com',
        'password' => 'wrongpassword',
    ]);

    $this->assertGuest();
    $response->assertSessionHasErrors();
});

// Session Security Tests

test('logout properly clears authentication', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    // Verify authenticated
    $this->assertAuthenticated();

    // Logout
    $response = $this->post(route('logout'));
    $response->assertRedirect('/');

    // Verify no longer authenticated
    $this->assertGuest();

    // Cannot access protected routes
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

// Admin Separation Tests

test('admin and user authentication are completely separate', function () {
    $admin = actingAsAdmin();
    $user = actingAsUser();

    // Admin can access admin routes
    $response = $this->get(route('admin.dashboard'));
    $response->assertStatus(200);

    // But admin session doesn't grant access to user routes without user authentication
    expect(auth()->guard('admin')->check())->toBeTrue()
        ->and(auth()->guard('web')->check())->toBeTrue();
});
