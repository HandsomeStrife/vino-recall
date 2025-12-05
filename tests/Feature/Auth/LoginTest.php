<?php

declare(strict_types=1);

use Domain\User\Models\User;

test('login page can be rendered', function () {
    $response = $this->get(route('login'));

    $response->assertStatus(200);
    $response->assertSee('Welcome back');
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => \Illuminate\Support\Facades\Hash::make('password'),
    ]);

    $response = $this->post(route('login'), [
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard'));
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => \Illuminate\Support\Facades\Hash::make('password'),
    ]);

    $this->post(route('login'), [
        'email' => 'test@example.com',
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('users can logout', function () {
    $user = actingAsUser();

    $response = $this->post(route('logout'));

    $this->assertGuest();
    $response->assertRedirect('/');
});
