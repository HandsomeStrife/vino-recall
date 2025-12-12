<?php

declare(strict_types=1);

use Domain\Admin\Models\Admin;
use Domain\User\Models\User;

test('admin and user can be logged in simultaneously on different guards', function () {
    $admin = Admin::factory()->create(['email' => 'admin@test.com']);
    $user = User::factory()->create(['email' => 'user@test.com']);

    // Login as user on web guard
    auth()->guard('web')->login($user);

    // Login as admin on admin guard
    auth()->guard('admin')->login($admin);

    // Both should be authenticated on their respective guards
    expect(auth()->guard('web')->check())->toBeTrue()
        ->and(auth()->guard('web')->id())->toBe($user->id)
        ->and(auth()->guard('admin')->check())->toBeTrue()
        ->and(auth()->guard('admin')->id())->toBe($admin->id);
});

test('logging out from one guard does not affect the other', function () {
    $admin = Admin::factory()->create();
    $user = User::factory()->create();

    auth()->guard('web')->login($user);
    auth()->guard('admin')->login($admin);

    // Logout user
    auth()->guard('web')->logout();

    // User should be logged out, admin should still be logged in
    expect(auth()->guard('web')->check())->toBeFalse()
        ->and(auth()->guard('admin')->check())->toBeTrue();
});

test('admin cannot access user routes without user authentication', function () {
    actingAsAdmin();

    // Admin routes should work
    $response = $this->get(route('admin.dashboard'));
    $response->assertStatus(200);

    // But user routes require web guard authentication
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('user cannot access admin routes', function () {
    actingAsUser();

    // User routes should work
    $response = $this->get(route('dashboard'));
    $response->assertStatus(200);

    // Admin routes should redirect to admin login
    $response = $this->get(route('admin.dashboard'));
    $response->assertRedirect(route('admin.login'));
});

test('admin factory creates valid admin', function () {
    $admin = Admin::factory()->create();

    expect($admin)->toBeInstanceOf(Admin::class)
        ->and($admin->email)->not->toBeNull()
        ->and($admin->password)->not->toBeNull();
});

test('user factory creates valid user', function () {
    $user = User::factory()->create();

    expect($user)->toBeInstanceOf(User::class)
        ->and($user->email)->not->toBeNull()
        ->and($user->password)->not->toBeNull();
});

test('admin table and user table are separate', function () {
    $admin = Admin::factory()->create(['email' => 'test@example.com']);
    $user = User::factory()->create(['email' => 'test@example.com']);

    // Both should exist with same email in different tables
    expect($admin->email)->toBe('test@example.com')
        ->and($user->email)->toBe('test@example.com')
        ->and($admin->id)->not->toBe($user->id);
});

test('admin repository works with admin guard', function () {
    $admin = actingAsAdmin();

    $repository = new \Domain\Admin\Repositories\AdminRepository;
    $adminData = $repository->getLoggedInAdmin();

    expect($adminData->id)->toBe($admin->id);
});

test('user repository works with user guard', function () {
    $user = actingAsUser();

    $repository = new \Domain\User\Repositories\UserRepository;
    $userData = $repository->getLoggedInUser();

    expect($userData->id)->toBe($user->id);
});

test('complete admin journey from registration to accessing dashboard', function () {
    // Create an admin
    $admin = Admin::factory()->create([
        'email' => 'admin@journey.com',
        'password' => \Illuminate\Support\Facades\Hash::make('password123'),
    ]);

    // Login
    $response = $this->post(route('admin.login'), [
        'email' => 'admin@journey.com',
        'password' => 'password123',
    ]);

    $response->assertRedirect(route('admin.dashboard'));

    // Access admin dashboard
    $response = $this->get(route('admin.dashboard'));
    $response->assertStatus(200);

    // Access admin routes
    $response = $this->get(route('admin.users'));
    $response->assertStatus(200);

    $response = $this->get(route('admin.decks'));
    $response->assertStatus(200);
});

test('complete user journey from registration to studying', function () {
    // Register a user
    $response = $this->post(route('register'), [
        'name' => 'Test User',
        'email' => 'user@journey.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertRedirect(route('dashboard'));

    // Access user dashboard
    $response = $this->get(route('dashboard'));
    $response->assertStatus(200);

    // Access study routes
    $response = $this->get(route('library'));
    $response->assertStatus(200);
});
