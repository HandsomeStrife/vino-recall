<?php

declare(strict_types=1);

use Domain\Admin\Models\Admin;
use Illuminate\Support\Facades\Hash;

use function Pest\Laravel\assertAuthenticatedAs;
use function Pest\Laravel\assertGuest;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

test('admin can view login page', function () {
    get(route('admin.login'))
        ->assertOk()
        ->assertSee('Admin Login');
});

test('admin can login with valid credentials', function () {
    $admin = Admin::factory()->create([
        'email' => 'admin@test.com',
        'password' => Hash::make('password123'),
    ]);

    post(route('admin.login'), [
        'email' => 'admin@test.com',
        'password' => 'password123',
    ])
        ->assertRedirect(route('admin.dashboard'));

    assertAuthenticatedAs($admin, 'admin');
});

test('admin cannot login with invalid credentials', function () {
    Admin::factory()->create([
        'email' => 'admin@test.com',
        'password' => Hash::make('password123'),
    ]);

    post(route('admin.login'), [
        'email' => 'admin@test.com',
        'password' => 'wrongpassword',
    ])
        ->assertSessionHasErrors('email');

    assertGuest('admin');
});

test('admin cannot login with non-existent email', function () {
    post(route('admin.login'), [
        'email' => 'nonexistent@test.com',
        'password' => 'password123',
    ])
        ->assertSessionHasErrors('email');

    assertGuest('admin');
});

test('admin login requires email', function () {
    post(route('admin.login'), [
        'password' => 'password123',
    ])
        ->assertSessionHasErrors('email');
});

test('admin login requires password', function () {
    post(route('admin.login'), [
        'email' => 'admin@test.com',
    ])
        ->assertSessionHasErrors('password');
});

test('admin can logout', function () {
    $admin = actingAsAdmin();

    post(route('admin.logout'))
        ->assertRedirect(route('admin.login'));

    assertGuest('admin');
});

test('admin login with remember me', function () {
    $admin = Admin::factory()->create([
        'email' => 'admin@test.com',
        'password' => Hash::make('password123'),
    ]);

    post(route('admin.login'), [
        'email' => 'admin@test.com',
        'password' => 'password123',
        'remember' => true,
    ])
        ->assertRedirect(route('admin.dashboard'));

    assertAuthenticatedAs($admin, 'admin');
    
    // Verify remember token was set
    $admin->refresh();
    expect($admin->remember_token)->not->toBeNull();
});

test('authenticated admin cannot access login page', function () {
    actingAsAdmin();

    // Authenticated admins should be redirected away from login
    // Laravel redirects to intended or home by default
    get(route('admin.login'))
        ->assertRedirect();
});

test('regular user cannot login to admin portal', function () {
    // Create a regular user
    \Domain\User\Models\User::factory()->create([
        'email' => 'user@test.com',
        'password' => Hash::make('password123'),
    ]);

    // Try to login to admin portal with user credentials
    post(route('admin.login'), [
        'email' => 'user@test.com',
        'password' => 'password123',
    ])
        ->assertSessionHasErrors('email');

    assertGuest('admin');
});

test('admin and user sessions are independent', function () {
    // Create both admin and user
    $admin = Admin::factory()->create([
        'email' => 'admin@test.com',
        'password' => Hash::make('password123'),
    ]);

    $user = \Domain\User\Models\User::factory()->create([
        'email' => 'user@test.com',
        'password' => Hash::make('password123'),
    ]);

    // Login as user
    post(route('login'), [
        'email' => 'user@test.com',
        'password' => 'password123',
    ]);

    // User should be authenticated on web guard, but not admin guard
    assertAuthenticatedAs($user, 'web');
    assertGuest('admin');

    // Login as admin
    post(route('admin.login'), [
        'email' => 'admin@test.com',
        'password' => 'password123',
    ]);

    // Admin should be authenticated on admin guard
    assertAuthenticatedAs($admin, 'admin');
    // User should still be authenticated on web guard
    assertAuthenticatedAs($user, 'web');
});

