<?php

declare(strict_types=1);

use Domain\User\Models\User;

// Registration Validation Tests

test('registration requires name', function () {
    $response = $this->post(route('register'), [
        'name' => '',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertSessionHasErrors('name');
    $this->assertGuest();
});

test('registration requires valid email format', function () {
    $response = $this->post(route('register'), [
        'name' => 'Test User',
        'email' => 'not-an-email',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

test('registration requires password', function () {
    $response = $this->post(route('register'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => '',
        'password_confirmation' => '',
    ]);

    $response->assertSessionHasErrors('password');
    $this->assertGuest();
});

test('registration requires password confirmation to match', function () {
    $response = $this->post(route('register'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'different456',
    ]);

    $response->assertSessionHasErrors('password');
    $this->assertGuest();
});

test('registration rejects duplicate email', function () {
    User::factory()->create(['email' => 'existing@example.com']);

    $response = $this->post(route('register'), [
        'name' => 'Test User',
        'email' => 'existing@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertSessionHasErrors('email');
});

test('registration validates email is string', function () {
    $response = $this->post(route('register'), [
        'name' => 'Test User',
        'email' => ['not', 'a', 'string'],
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertSessionHasErrors('email');
});

test('registration validates name max length', function () {
    $response = $this->post(route('register'), [
        'name' => str_repeat('a', 256), // 256 characters, should exceed max
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertSessionHasErrors('name');
});

// Login Validation Tests

test('login requires email', function () {
    $response = $this->post(route('login'), [
        'email' => '',
        'password' => 'password',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

test('login requires password', function () {
    $response = $this->post(route('login'), [
        'email' => 'test@example.com',
        'password' => '',
    ]);

    $response->assertSessionHasErrors('password');
    $this->assertGuest();
});

test('login validates email format', function () {
    $response = $this->post(route('login'), [
        'email' => 'not-an-email',
        'password' => 'password',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

test('login rejects non-existent email', function () {
    $response = $this->post(route('login'), [
        'email' => 'nonexistent@example.com',
        'password' => 'password',
    ]);

    $response->assertSessionHasErrors();
    $this->assertGuest();
});

// Password Reset Validation Tests

test('password reset requires email', function () {
    $response = $this->post(route('password.email'), [
        'email' => '',
    ]);

    $response->assertSessionHasErrors('email');
});

test('password reset requires valid email format', function () {
    $response = $this->post(route('password.email'), [
        'email' => 'not-an-email',
    ]);

    $response->assertSessionHasErrors('email');
});

test('password reset reset requires token', function () {
    $response = $this->post(route('password.update'), [
        'token' => '',
        'email' => 'test@example.com',
        'password' => 'newpassword',
        'password_confirmation' => 'newpassword',
    ]);

    $response->assertSessionHasErrors('token');
});

test('password reset requires matching password confirmation', function () {
    $response = $this->post(route('password.update'), [
        'token' => 'fake-token',
        'email' => 'test@example.com',
        'password' => 'newpassword',
        'password_confirmation' => 'different',
    ]);

    $response->assertSessionHasErrors('password');
});

// Input Sanitization Tests

test('registration trims whitespace from name', function () {
    $response = $this->post(route('register'), [
        'name' => '  Test User  ',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertRedirect(route('dashboard'));
    
    $user = User::where('email', 'test@example.com')->first();
    expect($user->name)->toBe('Test User');
});

test('registration accepts uppercase email', function () {
    $response = $this->post(route('register'), [
        'name' => 'Test User',
        'email' => 'TEST@EXAMPLE.COM',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertRedirect(route('dashboard'));
    $this->assertAuthenticated();
});

// XSS Prevention Tests

test('registration escapes html in name', function () {
    $maliciousName = '<script>alert("xss")</script>';
    
    $response = $this->post(route('register'), [
        'name' => $maliciousName,
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertRedirect(route('dashboard'));
    
    $user = User::where('email', 'test@example.com')->first();
    
    // When displayed, it should be escaped
    $this->actingAs($user);
    $response = $this->get(route('profile'));
    $response->assertDontSee('<script>', false); // false = don't escape for assertion
});

// Edge Case Validation Tests

test('registration handles very long email', function () {
    $longEmail = str_repeat('a', 256) . '@example.com';
    
    $response = $this->post(route('register'), [
        'name' => 'Test User',
        'email' => $longEmail,
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertSessionHasErrors('email');
});

test('registration handles unicode characters in name', function () {
    $response = $this->post(route('register'), [
        'name' => 'Test Üser 测试',
        'email' => 'unicode@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertRedirect(route('dashboard'));
    
    $user = User::where('email', 'unicode@example.com')->first();
    expect($user->name)->toBe('Test Üser 测试');
});

test('registration handles special characters in password', function () {
    $specialPassword = 'P@ssw0rd!#$%^&*()';
    
    $response = $this->post(route('register'), [
        'name' => 'Test User',
        'email' => 'special@example.com',
        'password' => $specialPassword,
        'password_confirmation' => $specialPassword,
    ]);

    $response->assertRedirect(route('dashboard'));
    $this->assertAuthenticated();
});

test('login is case insensitive for email', function () {
    User::factory()->create([
        'email' => 'test@example.com',
        'password' => \Illuminate\Support\Facades\Hash::make('password'),
    ]);

    $response = $this->post(route('login'), [
        'email' => 'TEST@EXAMPLE.COM',
        'password' => 'password',
    ]);

    $response->assertRedirect(route('dashboard'));
    $this->assertAuthenticated();
});

