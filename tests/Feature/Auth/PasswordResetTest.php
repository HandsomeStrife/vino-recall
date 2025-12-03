<?php

declare(strict_types=1);

use Domain\User\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

test('password reset link screen can be rendered', function () {
    $response = $this->get(route('password.request'));

    $response->assertStatus(200);
    $response->assertSee('Reset Password');
});

test('password reset link can be requested', function () {
    $user = User::factory()->create();

    $response = $this->post(route('password.email'), [
        'email' => $user->email,
    ]);

    $response->assertSessionHas('status');
});

test('password can be reset with valid token', function () {
    $user = User::factory()->create();

    $token = Password::createToken($user);

    $response = $this->post(route('password.update'), [
        'token' => $token,
        'email' => $user->email,
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ]);

    $response->assertSessionHas('status');
    $response->assertRedirect(route('login'));

    expect(Hash::check('new-password', $user->fresh()->password))->toBeTrue();
});
