<?php

declare(strict_types=1);

use Domain\Admin\Models\Admin;
use Domain\User\Models\User;
use Illuminate\Support\Facades\RateLimiter;

beforeEach(function () {
    // Clear rate limiter before each test
    RateLimiter::clear('login:127.0.0.1:test@example.com');
    RateLimiter::clear('admin.login:127.0.0.1:admin@example.com');
});

// User Login Rate Limiting

test('user login allows up to 5 attempts', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('correctpassword'),
    ]);

    // First 5 attempts with wrong password should fail but not be rate limited
    for ($i = 0; $i < 5; $i++) {
        $response = $this->post(route('login'), [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('email');
    }

    // 6th attempt should be rate limited
    $response = $this->post(route('login'), [
        'email' => 'test@example.com',
        'password' => 'wrongpassword',
    ]);

    $response->assertSessionHasErrors('email');
    expect($response->exception->errors()['email'][0])->toContain('Too many login attempts');
});

test('successful user login clears rate limiter', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('correctpassword'),
    ]);

    // Make 4 failed attempts
    for ($i = 0; $i < 4; $i++) {
        $this->post(route('login'), [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);
    }

    // Successful login should clear the rate limiter
    $response = $this->post(route('login'), [
        'email' => 'test@example.com',
        'password' => 'correctpassword',
    ]);

    $response->assertRedirect(route('dashboard'));
    $this->assertAuthenticated();

    // Logout
    $this->post(route('logout'));

    // Should be able to make 5 more attempts without being rate limited
    for ($i = 0; $i < 5; $i++) {
        $response = $this->post(route('login'), [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('email');
        expect($response->exception->errors()['email'][0])->not->toContain('Too many login attempts');
    }
});

test('rate limiting is per email and IP combination', function () {
    $user1 = User::factory()->create([
        'email' => 'user1@example.com',
        'password' => bcrypt('password'),
    ]);

    $user2 = User::factory()->create([
        'email' => 'user2@example.com',
        'password' => bcrypt('password'),
    ]);

    // Make 5 failed attempts for user1
    for ($i = 0; $i < 5; $i++) {
        $this->post(route('login'), [
            'email' => 'user1@example.com',
            'password' => 'wrongpassword',
        ]);
    }

    // user1 should be rate limited
    $response = $this->post(route('login'), [
        'email' => 'user1@example.com',
        'password' => 'wrongpassword',
    ]);
    expect($response->exception->errors()['email'][0])->toContain('Too many login attempts');

    // user2 should NOT be rate limited (different email)
    $response = $this->post(route('login'), [
        'email' => 'user2@example.com',
        'password' => 'wrongpassword',
    ]);
    expect($response->exception->errors()['email'][0])->not->toContain('Too many login attempts');
});

// Admin Login Rate Limiting

test('admin login allows up to 3 attempts', function () {
    $admin = Admin::factory()->create([
        'email' => 'admin@example.com',
        'password' => bcrypt('correctpassword'),
    ]);

    // First 3 attempts with wrong password should fail but not be rate limited
    for ($i = 0; $i < 3; $i++) {
        $response = $this->post(route('admin.login'), [
            'email' => 'admin@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('email');
    }

    // 4th attempt should be rate limited
    $response = $this->post(route('admin.login'), [
        'email' => 'admin@example.com',
        'password' => 'wrongpassword',
    ]);

    $response->assertSessionHasErrors('email');
    expect($response->exception->errors()['email'][0])->toContain('Too many login attempts');
});

test('successful admin login clears rate limiter', function () {
    $admin = Admin::factory()->create([
        'email' => 'admin@example.com',
        'password' => bcrypt('correctpassword'),
    ]);

    // Make 2 failed attempts
    for ($i = 0; $i < 2; $i++) {
        $this->post(route('admin.login'), [
            'email' => 'admin@example.com',
            'password' => 'wrongpassword',
        ]);
    }

    // Successful login should clear the rate limiter
    $response = $this->post(route('admin.login'), [
        'email' => 'admin@example.com',
        'password' => 'correctpassword',
    ]);

    $response->assertRedirect(route('admin.dashboard'));
    $this->assertAuthenticatedAs($admin, 'admin');

    // Logout
    $this->post(route('admin.logout'));

    // Should be able to make 3 more attempts without being rate limited
    for ($i = 0; $i < 3; $i++) {
        $response = $this->post(route('admin.login'), [
            'email' => 'admin@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('email');
        expect($response->exception->errors()['email'][0])->not->toContain('Too many login attempts');
    }
});

test('admin rate limiting is stricter than user rate limiting', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);

    $admin = Admin::factory()->create([
        'email' => 'admin@example.com',
        'password' => bcrypt('password'),
    ]);

    // User: Can make 5 failed attempts before being limited
    for ($i = 0; $i < 5; $i++) {
        $response = $this->post(route('login'), [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);
        
        if ($i < 4) {
            expect($response->exception->errors()['email'][0])->not->toContain('Too many login attempts');
        }
    }

    // Admin: Can only make 3 failed attempts before being limited
    for ($i = 0; $i < 4; $i++) {
        $response = $this->post(route('admin.login'), [
            'email' => 'admin@example.com',
            'password' => 'wrongpassword',
        ]);
        
        if ($i < 3) {
            expect($response->exception->errors()['email'][0])->not->toContain('Too many login attempts');
        } else {
            expect($response->exception->errors()['email'][0])->toContain('Too many login attempts');
        }
    }
});

