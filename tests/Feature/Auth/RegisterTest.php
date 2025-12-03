<?php

declare(strict_types=1);

use Domain\User\Models\User;

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertStatus(200);
    $response->assertSee('Create your account');
});

test('new users can register', function () {
    $response = $this->post(route('register'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard'));

    $user = User::where('email', 'test@example.com')->first();
    expect($user)->not->toBeNull();
    expect($user->name)->toBe('Test User');
});

test('users cannot register with invalid email', function () {
    $response = $this->post(route('register'), [
        'name' => 'Test User',
        'email' => 'invalid-email',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertGuest();
    $response->assertSessionHasErrors('email');
});

test('users cannot register with mismatched passwords', function () {
    $response = $this->post(route('register'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'different-password',
    ]);

    $this->assertGuest();
    $response->assertSessionHasErrors('password');
});
