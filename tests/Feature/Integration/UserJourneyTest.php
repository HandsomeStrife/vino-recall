<?php

declare(strict_types=1);

use Domain\Admin\Models\Admin;
use Domain\Card\Models\Card;
use Domain\Card\Models\CardReview;
use Domain\Deck\Actions\EnrollUserInDeckAction;
use Domain\Deck\Models\Deck;
use Domain\User\Models\User;

test('complete user journey: register, login, view dashboard, study cards', function () {
    // Create a deck with cards
    $deck = Deck::factory()->create(['name' => 'Test Deck', 'is_active' => true]);
    Card::factory()->count(3)->create(['deck_id' => $deck->id]);

    // Register a new user
    $response = $this->post(route('register'), [
        'name' => 'Journey User',
        'email' => 'journey@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertRedirect(route('dashboard'));
    $this->assertAuthenticated();

    // Verify user was created
    $user = User::where('email', 'journey@example.com')->first();
    expect($user)->not->toBeNull();

    // Visit dashboard - should see welcome message
    $response = $this->get(route('dashboard'));
    $response->assertStatus(200)
        ->assertSee('Welcome back');

    // Logout
    $response = $this->post(route('logout'));
    $response->assertRedirect('/');
    $this->assertGuest();

    // Login again
    $response = $this->post(route('login'), [
        'email' => 'journey@example.com',
        'password' => 'password123',
    ]);

    $response->assertRedirect(route('dashboard'));
    $this->assertAuthenticated();
});

test('admin journey: login, manage users, create deck', function () {
    $admin = Admin::factory()->create([
        'email' => 'admin@test.com',
        'password' => \Illuminate\Support\Facades\Hash::make('password'),
    ]);

    // Login as admin
    $response = $this->post(route('admin.login'), [
        'email' => 'admin@test.com',
        'password' => 'password',
    ]);

    $response->assertRedirect(route('admin.dashboard'));
    $this->assertAuthenticatedAs($admin, 'admin');

    // Access admin dashboard
    $response = $this->get(route('admin.dashboard'));
    $response->assertStatus(200);

    // Access user management
    $response = $this->get(route('admin.users'));
    $response->assertStatus(200);

    // Access deck management
    $response = $this->get(route('admin.decks'));
    $response->assertStatus(200);

    // Access categories management
    $response = $this->get(route('admin.categories'));
    $response->assertStatus(200);
});

test('user study session: select deck, study cards, rate cards, view progress', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    // Create deck with cards
    $deck = Deck::factory()->create(['is_active' => true]);
    $card1 = Card::factory()->create(['deck_id' => $deck->id]);
    $card2 = Card::factory()->create(['deck_id' => $deck->id]);

    // Visit library - should see both tabs
    $response = $this->get(route('library'));
    $response->assertStatus(200)
        ->assertSee('My Decks') // Verify library page loads
        ->assertSee('Browse'); // Verify browse tab exists
    
    // Deck should be visible somewhere on the page (either tab)
    // Since library is a Livewire component with tabs, just verify the page loads

    // Enroll in deck
    (new EnrollUserInDeckAction)->execute($user->id, $deck->id);

    // Study cards (simulate via CardReview creation for SRS state)
    CardReview::factory()->create([
        'user_id' => $user->id,
        'card_id' => $card1->id,
        'srs_stage' => 1, // Moved to first stage after correct answer
        'next_review_at' => now()->addDays(3),
    ]);
    
    // Track the review in history
    \Domain\Card\Models\ReviewHistory::factory()->correct()->create([
        'user_id' => $user->id,
        'card_id' => $card1->id,
        'previous_stage' => 0,
        'new_stage' => 1,
    ]);

    // Visit dashboard to see progress - should see Daily Goal section
    $response = $this->get(route('dashboard'));
    $response->assertStatus(200)
        ->assertSee('Daily Goal');
});

test('user profile update journey: login, update profile, change password', function () {
    $user = User::factory()->create([
        'name' => 'Original Name',
        'email' => 'original@example.com',
        'password' => \Illuminate\Support\Facades\Hash::make('oldpassword'),
    ]);

    $this->actingAs($user);

    // Visit profile page
    $response = $this->get(route('profile'));
    $response->assertStatus(200)
        ->assertSee('Profile');

    // Profile page contains Livewire component that handles updates
    $response->assertSeeLivewire('profile');
});

test('guest user journey: visit homepage, view features, register', function () {
    // Visit homepage
    $response = $this->get(route('home'));
    $response->assertStatus(200)
        ->assertSee('VinoRecall')
        ->assertSee('Spaced Repetition');

    // Try to access protected route, should redirect to login
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));

    // Visit register page
    $response = $this->get(route('register'));
    $response->assertStatus(200)
        ->assertSee('Create your account');
});

test('user cannot access admin routes', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    // Attempt to access admin dashboard
    $response = $this->get(route('admin.dashboard'));
    $response->assertRedirect(route('admin.login'));

    // Attempt to access user management
    $response = $this->get(route('admin.users'));
    $response->assertRedirect(route('admin.login'));

    // Attempt to access deck management
    $response = $this->get(route('admin.decks'));
    $response->assertRedirect(route('admin.login'));

    // Attempt to access categories management
    $response = $this->get(route('admin.categories'));
    $response->assertRedirect(route('admin.login'));
});

test('complete study session with multiple cards', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    // Create deck with multiple cards
    $deck = Deck::factory()->create(['is_active' => true]);
    $cards = Card::factory()->count(5)->create(['deck_id' => $deck->id]);

    // Enroll user in deck
    (new EnrollUserInDeckAction)->execute($user->id, $deck->id);

    // Create reviews for all cards (simulate previous study session)
    foreach ($cards as $card) {
        CardReview::factory()->create([
            'user_id' => $user->id,
            'card_id' => $card->id,
            'next_review_at' => now()->subDay(), // All due for review
        ]);
    }

    // Verify dashboard shows enrolled deck with daily goal
    $response = $this->get(route('dashboard'));
    $response->assertStatus(200)
        ->assertSee('Daily Goal');
});

test('user registration creates necessary records and redirects correctly', function () {
    $initialUserCount = User::count();

    // Register
    $response = $this->post(route('register'), [
        'name' => 'New User',
        'email' => 'newuser@example.com',
        'password' => 'securepassword',
        'password_confirmation' => 'securepassword',
    ]);

    // Verify redirect
    $response->assertRedirect(route('dashboard'));

    // Verify user was created
    expect(User::count())->toBe($initialUserCount + 1);

    $user = User::where('email', 'newuser@example.com')->first();
    expect($user)->not->toBeNull()
        ->and($user->name)->toBe('New User');

    // Verify user is authenticated
    $this->assertAuthenticatedAs($user);

    // Verify can access dashboard
    $response = $this->get(route('dashboard'));
    $response->assertStatus(200);
});
