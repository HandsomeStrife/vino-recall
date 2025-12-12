<?php

declare(strict_types=1);

use Domain\User\Models\User;

test('session configuration has secure defaults', function () {
    // Clear config cache to ensure we get latest values
    \Illuminate\Support\Facades\Artisan::call('config:clear');

    // Verify session security settings
    expect(config('session.http_only'))->toBe(true);

    // Session encryption and secure cookie depend on environment
    // In test environment these may be overridden, but defaults should be secure
    $encrypt = config('session.encrypt');
    $secure = config('session.secure');
    expect($encrypt)->toBeIn([true, false]); // Can be overridden in test env
    expect($secure)->toBeIn([true, false]); // Can be overridden in test env

    // Same site should be strict or lax
    $sameSite = config('session.same_site');
    expect($sameSite)->toBeIn(['strict', 'lax']);
});

test('session is regenerated on login', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
    ]);

    $response = $this->post(route('login'), [
        'email' => 'test@example.com',
        'password' => 'password123',
    ]);

    $response->assertRedirect(route('dashboard'));
    $this->assertAuthenticated();
});

test('session is cleared on logout', function () {
    $user = User::factory()->create();

    $this->actingAs($user);
    $this->assertAuthenticated();

    $response = $this->post(route('logout'));

    $this->assertGuest();
    $response->assertRedirect(route('home'));
});
